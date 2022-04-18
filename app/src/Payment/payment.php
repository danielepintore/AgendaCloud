<?php

class Payment {
    /**
     * @return array
     * @throws DatabaseException
     */
    public static function getPaymentMethods(Database $db) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT * FROM MetodoPagamento WHERE IsActive = TRUE";
        $status = $db->query($sql);
        if ($status) {
            //Success
            $result = $db->getResult();
            $response = [];
            foreach ($result as $r) {
                $response[] = array("id" => $r["id"], "name" => $r["Nome"]);
            }
            return $response;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }

    public static function isAValidMethod($methodId) { //todo check with db
        // check if the id provided is valid, currently we only accept 2 values;
        if ($methodId < 1 || $methodId > 2) {
            // is invalid
            return false;
        } else {
            return true;
        }
    }

    public static function isCashSelected($methodId) {
        // credit card = 1
        // cash = 2
        // if we add multiple payment methods we need to update this method but because i have only cash and credit card is fine
        if ($methodId == 2) {
            return true;
        } else {
            return false;
        }
    }
}