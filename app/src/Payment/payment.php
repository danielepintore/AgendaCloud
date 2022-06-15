<?php

/**
 * This class contains all function used to manage all payment methods
 */

class Payment {
    /**
     * @throws DatabaseException
     * @return array{
     *     id: int,
     *     name: string
     *     }
     * Returns a list of ACTIVE payments method
     */
    public static function getActivePaymentMethods(Database $db): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT * FROM MetodoPagamento WHERE IsActive = TRUE";
        $status = $db->query($sql);
        if (!$status) {
            throw DatabaseException::queryExecutionFailed();
        }
        $result = $db->getResult();
        $response = [];
        foreach ($result as $r) {
            $response[] = array("id" => $r["id"], "name" => $r["Nome"]);
        }
        return $response;
    }

    /**
     * @throws DatabaseException
     * @return array{
     *     id: int,
     *     name: string,
     *     isActive: bool
     *     }
     * Returns the complete list of payments method available
     */
    public static function getPaymentMethods(Database $db): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
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
     * @throws DatabaseException
     * @return bool
     * Updates the status of a payment method identified by its id
     * Returns true on success and false on failure
     */
    public static function updatePaymentMethodStatus(Database $db, $paymentMethodId, $status): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        //cash cannot be disabled
        if ($paymentMethodId == CASH){
            return false;
        }
        // if credit card isn't configured we can't enable it
        if ($paymentMethodId == CREDIT_CARD &&
            (empty($config->stripe->secret_api_key) || empty($config->stripe->endpoint_secret))) {
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
     * @param Database $db
     * @param $methodId
     * @return bool
     * Given a specific identifier checks if the method is valid
     */
    public static function isAValidMethod(Database $db, $methodId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $config = Config::getConfig();
            $sql = "SELECT isActive FROM MetodoPagamento WHERE id = ?";
            $status = $db->query($sql, "i", $methodId);
            $result = $db->getResult();
            if ($status && $db->getAffectedRows() == 1) {
                // checks if the credit card payments are configured properly
                if ($methodId == CREDIT_CARD &&
                    (empty($config->stripe->secret_api_key) || empty($config->stripe->endpoint_secret))) {
                    return false;
                }
                if ($result[0]['isActive'] == 1) {
                    return true;
                }
            }
            return false;
        } catch (Exception $e){
            if (DEBUG){
                Debug::printException($e);
            }
            return false;
        }
    }

    /**
     * @param $methodId
     * @return bool
     * Given a method id check if is the one corresponding to cash
     */
    public static function isCashSelected($methodId): bool {
        /* *************************************************
        credit card = 1
        cash = 2
        More info can be found on Constants class

        if we add multiple payment methods we need to update this method but
        because we have only cash and credit card is fine
        ************************************************* */
        if ($methodId == CASH) {
            return true;
        } else {
            return false;
        }
    }
}