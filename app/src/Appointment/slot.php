<?php

class Slot {
    public static function getSlots($serviceId, $employeeId, $date) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            // ottengo informazioni sul servizio richiesto
            $sql = "SELECT Durata, OraInizio, OraFine, TempoPausa FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i', $serviceId);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $service_info = $result->fetch_assoc();
                // ottengo gli orari gia occupati
                $sql = "SELECT Appuntamento.OraInizio AS OraInizio, Appuntamento.OraFine AS OraFine FROM Appuntamento WHERE Appuntamento.Data = ? AND Appuntamento.Dipendente_id = ?;";
                $stmt = $db->prepare($sql);
                $stmt->bind_param('si', $date, $employeeId);
                if ($stmt->execute()) {
                    //Success
                    $result = $stmt->get_result();
                    // we need to initialize this variable because if we don't do this php will throw an exception
                    $orari = array(); // this variable will contain all already taken slots
                    foreach ($result as $r) {
                        $startDate = new DateTime($r["OraInizio"]);
                        $endDate = new DateTime($r["OraFine"]);
                        $orari[] = array("start_time" => $startDate->format('H:i'), "end_time" => $endDate->format('H:i'));
                    }
                    // generazione slots liberi
                    $total_interval_time = $service_info["Durata"] + $service_info["TempoPausa"];
                    $serviceDuration = new DateInterval("PT" . $service_info["Durata"] . "M");
                    $tempoIntervallo = new DateInterval("PT" . $total_interval_time . "M");
                    $waitInterval = new DateInterval("PT" . $service_info["TempoPausa"] . "M");
                    $date = new DateTime($service_info["OraInizio"]);
                    $endDate = new DateTime($service_info["OraFine"]);
                    $generated_slots = array();
                    do {
                        $interval = new Interval($date->format('H:i'), $serviceDuration, $waitInterval);
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
                    return array("error" => false, "response" => $generated_slots);
                } else {
                    return array("error" => true, "info" => "Contattare l'assistenza");
                }
            } else {
                return array("error" => true, "info" => "Contattare l'assistenza");
            }
        } catch (ErrorException $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }
    }
}