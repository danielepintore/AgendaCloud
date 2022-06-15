<?php

use Admin\User;

class Slot {
    /**
     * @param Database $db
     * @param $serviceId
     * @param $employeeId
     * @param $dateStr
     * @return array
     * @throws DateException
     * @throws DatabaseException
     * @throws ServiceException
     * @throws EmployeeException
     * This function given the service identifier, the employee id and the date gives
     * all the slots available
     */
    public static function getSlots(Database $db, $serviceId, $employeeId, $dateStr, $isUserAuthenticated = false) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        DateCheck::isValidDate($dateStr, $isUserAuthenticated);
        $weekDay = (new DateTime($dateStr))->format('N');
        $workingTimes = workingTimes::getWorkingTimes($db, $weekDay, $dateStr, $serviceId, $employeeId);
        // get holiday start and end time
        $holidayInfo = workingTimes::getHolidayTimes($db, $weekDay, $dateStr, $serviceId, $employeeId);
        // check if the employee is active
        if (\Admin\Employee::isActive($db, $employeeId)) {
            // get service data from the database
            $sql = "SELECT Durata, TempoPausa, BookableUntil FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
            $status = $db->query($sql, "i", $serviceId);
            if ($status) {
                //Success
                $result = $db->getResult();
                if ($db->getAffectedRows() == 1) { // The service exist
                    $service_info = $result[0];
                    // ottengo gli orari gia occupati
                    $sql = "SELECT Appuntamento.OraInizio AS OraInizio, Appuntamento.OraFine AS OraFine FROM Appuntamento WHERE Appuntamento.Data = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.Stato != ? AND Appuntamento.Stato != ? AND Appuntamento.Stato != ?";
                    $status = $db->query($sql, "siiii", $dateStr, $employeeId, PAYMENT_EXPIRED, REJECTED_BY_USER, CANCELED);
                    if ($status) {
                        //Success
                        $result = $db->getResult();
                        // we need to initialize this variable because if we don't do this php will throw an exception
                        $orari = []; // this variable will contain all already taken slots
                        foreach ($result as $r) {
                            try {
                                $startDate = new DateTime($r["OraInizio"]);
                                $endDate = new DateTime($r["OraFine"]);
                                $orari[] = array("start_time" => $startDate->format('H:i'), "end_time" => $endDate->format('H:i'));
                            } catch (Exception $e) {
                                throw DateException::wrongStartOrEndTime();
                            }
                        }
                        // if the user is authenticated we can disable the BookableUntil time interval
                        if($isUserAuthenticated){
                            $service_info["BookableUntil"] = 0;
                        }

                        // generazione slots liberi
                        $total_interval_time = $service_info["Durata"] + $service_info["TempoPausa"];
                        try {
                            $serviceDuration = new DateInterval("PT" . $service_info["Durata"] . "M");
                            $tempoIntervallo = new DateInterval("PT" . $total_interval_time . "M");
                            $waitInterval = new DateInterval("PT" . $service_info["TempoPausa"] . "M");
                            $bookableUntilInterval = new DateInterval("PT" . $service_info["BookableUntil"] . "M");
                        } catch (Exception $e) {
                            throw DateException::wrongIntervalString();
                        }
                        $date = DateTime::createFromFormat("Y-m-d G:i", $dateStr . " " . $workingTimes["startTime"]);
                        $endDate = DateTime::createFromFormat("Y-m-d G:i", $dateStr . " " . $workingTimes["endTime"]);
                        $now = new DateTime();
                        $generated_slots = array();
                        do {
                            $interval = new Interval($date->format('H:i'), $serviceDuration, $waitInterval);
                            // check if the service can be added due it's time, because if it's already too late we can't add it
                            $serviceStartTime = clone $date; // creates a new object
                            if ($now >= $serviceStartTime->sub($bookableUntilInterval)) {
                                $date->add($tempoIntervallo);
                                continue;
                            }
                            $isFree = true;
                            foreach ($orari as $o) {
                                if ($interval->getStartTime() < $o["start_time"] && $interval->getEndTime() <= $o["start_time"] ||
                                    $interval->getStartTime() > $o["start_time"] && $interval->getStartTime() >= $o["end_time"]) {
                                    // lo slot potrebbe essere libero
                                    //todo rimuovere la terza condizione ($interval->getStartTime() > $o["start_time"])
                                } else {
                                    // lo slot non è libero
                                    $isFree = false;
                                }
                            }
                            // se isFree è rimasto true significa che lo slot è compatibile con tutti gli appuntamenti gia presi
                            // quindi può essere inserito
                            if ($isFree) {
                                // we need to check if the end time of a slot is bigger than the actual service end time
                                if ($interval->getEndTime() <= $endDate->format('H:i')) {
                                    // we can add the slot
                                    // todo check if we are removing the wait time
                                    // before adding to the generated slot we remove the wait time because it shouldn't be visible by the client
                                    $generated_slots[] = $interval->getArray();
                                }
                            }
                            $date->add($tempoIntervallo);
                        } while ($date < $endDate);
                        // if there is a holidays remove some slots
                        if (sizeof($holidayInfo) > 0) {
                            $slotsWithHolidays = [];
                            foreach ($holidayInfo as $holiday) {
                                foreach ($generated_slots as $slot) {
                                    // add only the slots that arent included in the holiday timeframe
                                    if ($slot["start_time"] <= $holiday['startTime'] && $slot['end_time'] <= $holiday['startTime']) {
                                        $slotsWithHolidays[] = $slot;
                                    } elseif ($slot["start_time"] >= $holiday['endTime']) {
                                        $slotsWithHolidays[] = $slot;
                                    }
                                }
                                $generated_slots = $slotsWithHolidays;
                                $slotsWithHolidays = [];
                            }
                        }
                        return $generated_slots;
                    }
                } else {
                    throw ServiceException::serviceNotAvailable();
                }
            }
        } else {
            throw EmployeeException::employeeIsNotActive();
        }
        return [];
    }
}