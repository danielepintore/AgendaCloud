<?php
class Payment {
    public static function getPaymentMethods(){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            $sql = "SELECT * FROM MetodoPagamento WHERE IsActive = TRUE";
            $stmt = $db->prepare($sql);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r){
                    $response[] = array("id" => $r["id"], "name" => $r["Nome"]);
                }
                return(array("error" => false, "response" => $response));
            } else {
                return array("error" => true, "info" => "Contattare l'assistenza");
            }
        } catch (ErrorException $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }
    }

    public static function isAValidMethod($methodId){
        // check if the id provided is valid, currently we only accept 2 values
        if ($methodId < 1 || $methodId > 2) {
            // is invalid
            return false;
        } else {
            return true;
        }
    }
    public static function isCashSelected($methodId){
        // credit card = 1
        // cash = 2
        // if we add multiple payment methods we need to update this method but because i have only cash and credit card is fine
        if ($methodId == 2){
            return true;
        } else {
            return false;
        }
    }
}