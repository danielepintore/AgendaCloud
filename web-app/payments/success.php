<?php
include('../api/db.php');
    try {
        $db = getDB();
        if (isset($_GET['session_id'])){
            $sql = "UPDATE Appuntamento SET Stato = 'Payment success' WHERE SessionId = ?";
            $stmt = $db->prepare($sql);
            $stmt ->bind_param('s', $_GET['session_id']);
            if ($stmt->execute()) {
                //Success
                //pagamento confermato
                print "pagamento confermato";
            } else {
                //errore nel pagamento
            }
        }
    } catch (ErrorException $e) {
        return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
    }

