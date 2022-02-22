<?php

class Order {
    /**
     * @param $session_id
     * @return bool
     * @throws DatabaseException
     */
    public static function markAsPaid($session_id) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = "UPDATE Appuntamento SET Stato = 'Payment success' WHERE SessionId = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('s', $session_id)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            // pagamento confermato
            return true;
        } else {
            //errore nell'aggiornamento dello stato del pagamento da parte del db
            throw DatabaseException::updateOrderStatus();
        }
    }
}