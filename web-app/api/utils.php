<?php
include("db.php");

function print_log($val) {
    return file_put_contents('php://stderr', print_r($val, TRUE));
}

function get_services($serviceId = null){
    try {
        $db = getDB();
        if (isset($serviceId) && is_numeric($serviceId)){
            $sql = "SELECT * FROM Servizio WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('i', $serviceId);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = $result->fetch_assoc();
                return array("error" => false, "response" => $response);
            } else {
                return array("error" => true, "info" => "Contattare l'assistenza");
            }
        } else {
            $sql = "SELECT * FROM Servizio";
            $stmt = $db->prepare($sql);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r){
                    $response[] = $r;
                }
                return(array("error" => false, "response" => $response));
            } else {
                return array("error" => true, "info" => "Contattare l'assistenza");
            }
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}

function get_payments_provider(){
    try {
        $db = getDB();
        $sql = "SELECT * FROM MetodoPagamento WHERE Stato = TRUE";
        $stmt = $db->prepare($sql);
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $response = array();
            foreach ($result as $r){
                $response[] = $r;
            }
            return(array("error" => false, "response" => $response));
        } else {
            return array("error" => true, "info" => "Contattare l'assistenza");
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}

function get_dipendenti($service){
    try {
        $db = getDB();
        $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
        $stmt = $db->prepare($sql);
        $stmt ->bind_param('i', $service);
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $response = array();
            foreach ($result as $r){
                $response[] = $r;
            }
            return(array("error" => false, "response" => $response));
        } else {
            return array("error" => true, "info" => "Contattare l'assistenza");
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}

function get_slots($serviceId, $workerId, $date){
    try {
        $db = getDB();
        // ottengo informazioni sul servizio richiesto
        $sql = "SELECT Durata, OraInizio, OraFine, TempoPausa FROM Servizio WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt ->bind_param('i', $serviceId);
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $service_info = $result->fetch_assoc();
            // ottengo gli orari gia occupati
            $sql = "SELECT Appuntamento.OraInizio AS OraInizio, Appuntamento.OraFine AS OraFine FROM Appuntamento WHERE Appuntamento.Data = ? AND Appuntamento.Dipendente_id = ?;";
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('si', $date, $workerId);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                foreach ($result as $r){
                    $startDate = new DateTime($r["OraInizio"]);
                    $endDate = new DateTime($r["OraFine"]);
                    $orari[] = array("start_time" =>  $startDate-> format('H:i'), "end_time" => $endDate->format('H:i'));
                }
                // generazione slots liberi
                $total_inteval_time = $service_info["Durata"] + $service_info["TempoPausa"];
                $tempoIntevallo = new DateInterval("PT$total_inteval_time"."M");
                $date = new DateTime($service_info["OraInizio"]);
                $endDate = new DateTime($service_info["OraFine"]);
                do {
                    $interval = array("start_time" => $date->format('H:i'), "end_time" => $date->add($tempoIntevallo)->format('H:i'));
                    $isFree = true;
                    foreach ($orari as $o){
                        if ($interval["start_time"] < $o["start_time"] && $interval["end_time"] <= $o["start_time"] ||
                            $interval["start_time"] > $o["start_time"] && $interval["start_time"] >= $o["end_time"]){
                            // lo slot potrebbe essere libero
                        } else {
                            // lo slot non è libero
                            $isFree = false;
                        }
                    }
                    // se isFree è rimasto true significa che lo slot è compatibile con tutti gli appuntamenti gia presi
                    // quindi può essere inserito
                    if ($isFree){
                        $generated_slots[] = $interval;
                    }
                } while($date < $endDate);
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

function book($serviceId, $workerId, $date, $my_slot, $client, $session_id, $payment_status){
    try {
        $db = getDB();
        // all variables are set
        // add new client
        $sql = "INSERT INTO `Cliente` (`id`, `Nome`, `Cognome`, `CodiceFiscale`, `DataNascita`, `Email`, `Cellulare`) VALUES (NULL, ?, ?, NULL, NULL, ?, ?) ";
        $stmt = $db->prepare($sql);
        $stmt ->bind_param('ssss', $client["nome"], $client["cognome"], $client["email"], $client["phone"]);
        if ($stmt->execute()) {
            //Success
            $client_id = $stmt->insert_id;
            // check if the current request is generated by the api
            $slots = get_slots($serviceId, $workerId, $date);
            if (!$slots["error"] && count($slots["response"]) > 0){
                $slots = $slots["response"];
                $selected_slot = explode('-', $my_slot);
                $my_slot = array("start_time" => $selected_slot[0], "end_time" => $selected_slot[1]);
                $isAvailable = false;
                foreach ($slots as $s){
                    if ($s["start_time"] == $my_slot["start_time"] && $s["end_time"] == $my_slot["end_time"]){
                        $isAvailable = true;
                        break;
                    }
                }
                if ($isAvailable){
                    // slot presente tra quelli generati dall'api procedere con la prenotazione
                    $sql = "INSERT INTO Appuntamento (id, Cliente_id, Servizio_id, Dipendente_id, Data, OraInizio, OraFine, Stato, SessionId) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?) ";
                    $stmt = $db->prepare($sql);
                    $stmt ->bind_param('iiisssss', $client_id, $serviceId, $workerId, $date, $selected_slot[0], $selected_slot[1], $payment_status, $session_id);
                    if ($stmt->execute()) {
                        //Success
                        return array("error" => false, "response" => "ok");
                    } else {
                        return array("error" => true, "info" => "Contatta l'assistenza");
                    }
                } else {
                    // slot non esiste non inserire
                    return array("error" => true, "info" => "Slot non disponibile");
                }
            } else {
                //error from the method to get the slots availables
                return array("error" => true, "info" => "Contatta l'assistenza");
            }
        } else {
            return array("error" => true, "info" => "Contatta l'assistenza");
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}

function set_order_as_paid($session_id){
    try {
        $db = getDB();
        $sql = "UPDATE Appuntamento SET Stato = 'Payment success' WHERE SessionId = ?";
        $stmt = $db->prepare($sql);
        $stmt ->bind_param('s', $session_id);
        if ($stmt->execute()) {
            //Success
            //pagamento confermato
        } else {
            //errore nel pagamento
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}