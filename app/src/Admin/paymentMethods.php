<?php

namespace Admin;
class PaymentMethods {
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
        $sql = "UPDATE MetodoPagamento SET isActive = ? WHERE MetodoPagamento.id = ?";
        $status = $db->query($sql, "ii", $status, $paymentMethodId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }

    }
}