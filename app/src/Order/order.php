<?php

class Order {
    /**
     * @param Database $db
     * @param $session_id
     * @return bool
     * @throws DatabaseException
     */
    public static function markAsPaid(Database $db, $session_id): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "UPDATE Appuntamento SET Stato = ? WHERE (SessionId = ? AND Stato = ?)";
        $status = $db->query($sql, "isi", APPOINTMENT_CONFIRMED, $session_id, PAYMENT_PENDING);
        if ($status) {
            return true;
        } else {
            //errore nell'aggiornamento dello stato del pagamento da parte del db
            throw DatabaseException::updateOrderStatus();
        }
    }
}