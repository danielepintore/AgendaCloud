<?php

namespace Admin;

use Database;
use DatabaseException;

class Appointment {
    /**
     * @throws DatabaseException
     */
    public static function getAppointments($isAdmin, $date, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS TipoPagamento FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id) ORDER BY Appuntamento.OraInizio';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('s', $date)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'TipoPagamento' => $r['TipoPagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS TipoPagamento FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Appuntamento.Dipendente_id = ? AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id) ORDER BY Appuntamento.OraInizio';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('si', $date, $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'TipoPagamento' => $r['TipoPagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    }
}