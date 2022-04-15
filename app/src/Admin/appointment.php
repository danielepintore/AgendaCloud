<?php

namespace Admin;

use Database;
use DatabaseException;
use DateTime;

class Appointment {
    /**
     * @throws DatabaseException
     */
    public static function getAppointments($isAdmin, $date, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            if (\DateCheck::isToday($date)){
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            } else {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            }
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = APPOINTMENT_CONFIRMED;
            if (!$stmt->bind_param('si', $date, $appointmentStatus)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $date = DateTime::createFromFormat("Y-m-d", $r['Data']);
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Stato' => $r['Stato'], 'TipoPagamento' => $r['TipoPagamento'],
                        'Data' => $date->format('j/n/Y'));
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            if (\DateCheck::isToday($date)){
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            } else {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            }
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = APPOINTMENT_CONFIRMED;
            if (!$stmt->bind_param('isi', $employeeId, $date, $appointmentStatus)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $date = DateTime::createFromFormat("Y-m-d", $r['Data']);
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Stato' => $r['Stato'], 'TipoPagamento' => $r['TipoPagamento'],
                        'Data' => $date->format('j/n/Y'));
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    }
    public static function getAppointmentRequest($isAdmin, $employeeId = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.MetodoPagamento_id = ? AND Appuntamento.Stato = ? AND CONCAT(Appuntamento.Data, Appuntamento.OraInizio) >= CONCAT(CURDATE(), CURTIME())) ORDER BY Appuntamento.Data, Appuntamento.OraInizio';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $paymentMethod = CASH;
            $paymentStatus = WAITING_APPROVAL;
            if (!$stmt->bind_param('ii', $paymentMethod, $paymentStatus)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $date = DateTime::createFromFormat("Y-m-d", $r['Data']);
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Data' => $date->format('j/n/Y'));
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, Appuntamento.Data AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.MetodoPagamento_id = ? AND Appuntamento.Stato = ? AND CONCAT(Appuntamento.Data, Appuntamento.OraInizio) >= CONCAT(CURDATE(), CURTIME())) ORDER BY Appuntamento.Data, Appuntamento.OraInizio';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $paymentMethod = CASH;
            $paymentStatus = WAITING_APPROVAL;
            if (!$stmt->bind_param('iii', $employeeId, $paymentMethod, $paymentStatus)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $date = DateTime::createFromFormat("Y-m-d", $r['Data']);
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Data' => $date->format('j/n/Y'));
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    }
    public static function acceptAppointment($isAdmin, $appointmentId, $employeeId = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.MetodoPagamento_id = 2';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = APPOINTMENT_CONFIRMED;
            $waitingApprovalStauts = WAITING_APPROVAL;
            if (!$stmt->bind_param('iii', $appointmentStatus,$appointmentId, $waitingApprovalStauts)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                if ($stmt->affected_rows == 1){
                    return true;
                } else {
                    return false;
                }
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.MetodoPagamento_id = 2';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = APPOINTMENT_CONFIRMED;
            $waitingApprovalStauts = WAITING_APPROVAL;
            if (!$stmt->bind_param('iiii', $appointmentStatus,$appointmentId, $waitingApprovalStauts, $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                if ($stmt->affected_rows == 1){
                    return true;
                } else {
                    return false;
                }
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        }
    }

    public static function rejectAppointment($isAdmin, $appointmentId, $employeeId = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.MetodoPagamento_id = 2';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = REJECTED_BY_USER;
            $waitingApprovalStauts = WAITING_APPROVAL;
            if (!$stmt->bind_param('iii', $appointmentStatus,$appointmentId, $waitingApprovalStauts)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                if ($stmt->affected_rows == 1){
                    return true;
                } else {
                    return false;
                }
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.MetodoPagamento_id = 2';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = REJECTED_BY_USER;
            $waitingApprovalStauts = WAITING_APPROVAL;
            if (!$stmt->bind_param('iiii', $appointmentStatus,$appointmentId, $waitingApprovalStauts, $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                if ($stmt->affected_rows == 1){
                    return true;
                } else {
                    return false;
                }
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        }
    }

    public static function deleteAppointment($isAdmin, $appointmentId, $employeeId = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ?';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = CANCELED;
            if (!$stmt->bind_param('ii', $appointmentStatus,$appointmentId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                return true;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Dipendente_id = ?';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            $appointmentStatus = CANCELED;
            if (!$stmt->bind_param('iii', $appointmentStatus,$appointmentId, $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                return true;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
            return false;
        }
    }
    public static function fetchAppointmentInfo($appointmentId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'SELECT Cliente.Nome, Cliente.Email, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine FROM Appuntamento, Cliente WHERE (Appuntamento.id = ? AND Appuntamento.Cliente_id = Cliente.id);';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $appointmentId)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $result = $result->fetch_row();
            return (object) array("name" => $result[0], "email" => $result[1], "date" => $result[2], "startTime" => $result[3], "endTime" => $result[4]);
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }
    public static function fetchAppointmentInfoBySessionID($sessionId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'SELECT Cliente.Nome, Cliente.Email, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine FROM Appuntamento, Cliente WHERE (Appuntamento.SessionId = ? AND Appuntamento.Cliente_id = Cliente.id);';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('s', $sessionId)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $result = $result->fetch_row();
            return (object) array("name" => $result[0], "email" => $result[1], "date" => $result[2], "startTime" => $result[3], "endTime" => $result[4]);
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }
}