<?php

class Order {
    public static function markAsPaid($session_id) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            $sql = "UPDATE Appuntamento SET Stato = 'Payment success' WHERE SessionId = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('s', $session_id);
            if ($stmt->execute()) {
                //Success
                //pagamento confermato
            } else {
                //errore nel pagamento
            }
        } catch (Exception $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }
    }
}