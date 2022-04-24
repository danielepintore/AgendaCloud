<?php

class Payment {
    /**
     * @return array
     * @throws DatabaseException
     */
    public static function getActivePaymentMethods(Database $db) {
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

    /**
     * @return array
     * @throws \DatabaseException
     * Return all payments method availables
     */
    public static function getPaymentMethods(\Database $db) {
        $sql = "SELECT * FROM MetodoPagamento";
        $status = $db->query($sql);
        if ($status) {
            $result = $db->getResult();
            $paymentMethods = [];
            foreach ($result as $r) {
                //skip cash
                if ($r['id'] == CASH){
                    continue;
                }
                $paymentMethods[] = ["id" => $r['id'], "name" => $r['Nome'], "isActive" => $r['isActive']];
            }
            return $paymentMethods;
        } else {
            return [];
        }
    }

    /**
     * @throws \DatabaseException
     * Updates the status of a payment method
     */
    public static function updatePaymentMethodStatus(\Database $db, $paymentMethodId, $status) {
        //cash cannot be disabled
        if ($paymentMethodId == CASH){
            return false;
        }
        $sql = "UPDATE MetodoPagamento SET isActive = ? WHERE MetodoPagamento.id = ?";
        $status = $db->query($sql, "ii", $status, $paymentMethodId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Check if the payment method provided is valid and active
     */
    public static function isAValidMethod(Database $db, $methodId) {
        try {
            $sql = "SELECT isActive FROM MetodoPagamento WHERE id = ?";
            $status = $db->query($sql, "i", $methodId);
            $result = $db->getResult();
            if ($status && $db->getAffectedRows() == 1) {
                if ($result[0]['isActive'] == 1) {
                    return true;
                }
            }
        } catch (Exception $e){
            return false;
        }
        return false;
    }

    public static function isCashSelected($methodId) {
        // credit card = 1
        // cash = 2
        // if we add multiple payment methods we need to update this method but because i have only cash and credit card is fine
        if ($methodId == CASH) {
            return true;
        } else {
            return false;
        }
    }
}