<?php

class Order {
    /**
     * @param $session_id
     * @return bool
     * @throws DatabaseException
     */
    public static function markAsPaid($db, $session_id) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "UPDATE Appuntamento SET Stato = ? WHERE SessionId = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        $appointmentConfirmed = APPOINTMENT_CONFIRMED;
        if (!$stmt->bind_param('is', $appointmentConfirmed, $session_id)) {
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