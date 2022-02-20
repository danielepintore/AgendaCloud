<?php
class Services {
    public static function getAllServices() : array{
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
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
        } catch (ErrorException $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }
    }
}