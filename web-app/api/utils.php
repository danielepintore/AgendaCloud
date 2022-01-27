<?php
include("db.php");

function get_services(){
    try {
        $db = getDB();
        if (isset($_GET['service']) && is_numeric($_GET['service'])){
            $sql = "SELECT * FROM Servizio WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('i', $_GET['service']);
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

function get_dipendenti($service = null){
    try {
        $db = getDB();
        if (isset($_GET['service']) && is_numeric($_GET['service'])){
            $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('i', $_GET['service']);
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
        } else {
            return array("error" => true, "info" => "Contattare l'assistenza");
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}

function get_slots(){
    try {
        $db = getDB();
        if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId']) && isset($_GET['workerId']) && is_numeric($_GET['workerId']) && isset($_GET['date'])){
            // ottengo informazioni sul servizio richiesto
            $sql = "SELECT Durata, OraInizio, OraFine, TempoPausa FROM Servizio WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('i', $_GET['serviceId']);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $service_info = $result->fetch_assoc();
                // ottengo gli orari gia occupati
                $sql = "SELECT Appuntamento.OraInizio AS OraInizio, Appuntamento.OraFine AS OraFine FROM Appuntamento WHERE Appuntamento.Data = ? AND Appuntamento.Dipendente_id = ?;";
                $stmt = $db->prepare($sql);
                $stmt ->bind_param('si', $_GET['date'], $_GET['workerId']);
                if ($stmt->execute()) {
                    //Success
                    $result = $stmt->get_result();
                    foreach ($result as $r){
                        $orari[] = array("start_time" => $r["OraInizio"], "end_time" => $r["OraFine"]);
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
        } else {
            return array("error" => true);
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }
}