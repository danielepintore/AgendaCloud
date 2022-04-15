<?php

class Slot {
    /**
     * @param $serviceId
     * @param $employeeId
     * @param $dateStr
     * @return array
     * @throws DataException
     * @throws DatabaseException
     * @throws ServiceException
     */
    public static function getSlots($serviceId, $employeeId, $dateStr) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        DateCheck::isValidDate($dateStr);
        $db = Database::getDB();
        // ottengo informazioni sul servizio richiesto
        $sql = "SELECT Durata, OraInizio, OraFine, TempoPausa, BookableUntil FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $serviceId)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            if ($stmt->affected_rows == 1) {
                $service_info = $result->fetch_assoc();
                // ottengo gli orari gia occupati
                $sql = "SELECT Appuntamento.OraInizio AS OraInizio, Appuntamento.OraFine AS OraFine FROM Appuntamento WHERE Appuntamento.Data = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.Stato != ? AND Appuntamento.Stato != ? AND Appuntamento.Stato != ?";
                $stmt = $db->prepare($sql);
                if (!$stmt) {
                    throw DatabaseException::queryPrepareFailed();
                }
                $paymentExpired = PAYMENT_EXPIRED;
                $rejectedByUser = REJECTED_BY_USER;
                $canceled = CANCELED;
                if (!$stmt->bind_param('siiii', $dateStr, $employeeId, $paymentExpired, $rejectedByUser, $canceled)) {
                    throw DatabaseException::bindingParamsFailed();
                }
                if ($stmt->execute()) {
                    //Success
                    $result = $stmt->get_result();
                    // we need to initialize this variable because if we don't do this php will throw an exception
                    $orari = array(); // this variable will contain all already taken slots
                    foreach ($result as $r) {
                        try {
                            $startDate = new DateTime($r["OraInizio"]);
                            $endDate = new DateTime($r["OraFine"]);
                            $orari[] = array("start_time" => $startDate->format('H:i'), "end_time" => $endDate->format('H:i'));
                        } catch (Exception $e) {
                            throw DataException::wrongStartOrEndTime();
                        }
                    }
                    // generazione slots liberi
                    $total_interval_time = $service_info["Durata"] + $service_info["TempoPausa"];
                    try {
                        $serviceDuration = new DateInterval("PT" . $service_info["Durata"] . "M");
                        $tempoIntervallo = new DateInterval("PT" . $total_interval_time . "M");
                        $waitInterval = new DateInterval("PT" . $service_info["TempoPausa"] . "M");
                        $bookableUntilInterval = new DateInterval("PT" . $service_info["BookableUntil"] . "M");
                    } catch (Exception $e) {
                        throw DataException::wrongIntervalString();
                    }
                    $date = DateTime::createFromFormat("Y-m-d G:i:s", $dateStr . " " . $service_info["OraInizio"]);
                    $endDate = DateTime::createFromFormat("Y-m-d G:i:s", $dateStr . " " . $service_info["OraFine"]);
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
                                // before adding to the generated slot we remove the wait time because it shouldn't be visible by the client
                                $generated_slots[] = $interval->getArray();
                            }
                        }
                        $date->add($tempoIntervallo);
                    } while ($date < $endDate);
                    return $generated_slots;
                } else {
                    throw DatabaseException::queryExecutionFailed();
                }
            } else {
                throw ServiceException::serviceNotAvailable();
            }
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }
}