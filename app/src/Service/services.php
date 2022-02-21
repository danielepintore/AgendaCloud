<?php

class Services {
    public static function getAllServices(): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            $sql = "SELECT id, Nome, Durata, OraInizio, OraFine, Costo FROM Servizio WHERE(IsActive = TRUE)";
            $stmt = $db->prepare($sql);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return (array("error" => false, "response" => $response));
            } else {
                return array("error" => true, "info" => "Contattare l'assistenza");
            }
        } catch (Exception $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }
    }
}