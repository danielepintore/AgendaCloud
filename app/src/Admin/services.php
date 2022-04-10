<?php

namespace Admin;

use Database;
use DatabaseException;

class Services {
    /**
     * @throws DatabaseException
     */
    // gets the service that an employee is offering
    public static function getEmployeeService($isAdmin, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $db = Database::getDB();
            $sql = "SELECT id, Nome, Durata, OraInizio, OraFine, Costo FROM Servizio WHERE(IsActive = TRUE)";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            $db = Database::getDB();
            $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.OraInizio, Servizio.OraFine, Servizio.Costo FROM Servizio, Offre WHERE (Offre.Dipendente_id = ? AND Offre.Servizio_id = Servizio.id)";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('i', $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    }

    // Add a service
    public static function addServices($name, $duration, $startTime, $endTime, $cost, $waitTime, $bookableUntil, $isActive, $description = "") {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'INSERT INTO Servizio (id, Nome, Durata, OraInizio, OraFine, Costo, TempoPausa, Descrizione, ImmagineUrl, IsActive, BookableUntil) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?) ';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($isActive) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        if (!$stmt->bind_param('sissiisii', $name, $duration, $startTime, $endTime, $cost, $waitTime, $description, $isActive, $bookableUntil)) {
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

    // Get the list of services
    public static function getServiceList($id = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        if ($id == null) {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre WHERE Offre.Servizio_id=Servizio.id) AS NumDipendenti FROM Servizio';
        } else {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre WHERE Offre.Servizio_id=Servizio.id) AS NumDipendenti FROM Servizio WHERE Servizio.id = ?';
        }
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($id != null) {
            if (!$stmt->bind_param('i', $id)) {
                throw DatabaseException::bindingParamsFailed();
            }
        }
        if ($stmt->execute()) {
            //Success
            $services = array();
            $result = $stmt->get_result();
            foreach ($result as $r) {
                $services[] = array("id" => $r['id'], "name" => $r['Nome'], "duration" => $r['Durata'], "startTime" => $r['OraInizio'],
                    "endTime" => $r['OraFine'], "cost" => $r['Costo'], "waitTime" => $r['TempoPausa'], "description" => $r['Descrizione'],
                    "isActive" => $r['IsActive'], "bookableUntil" => $r['BookableUntil'], "employeesNumber" => $r['NumDipendenti']);
            }
            return $services;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        return false;
    }

    // Get the list of employees that offer a service
    public static function getEmployeeList($serviceId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente, Offre  WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $serviceId)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $employees = array();
            $result = $stmt->get_result();
            foreach ($result as $r) {
                $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome']);
            }
            return $employees;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        return false;
    }

    // update a service
    public static function updateService($serviceId, $name, $duration, $startTime, $endTime, $cost, $waitTime, $bookableUntil, bool $isActive, $description = "") {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'UPDATE Servizio SET Nome = ?, Durata = ?, OraInizio = ?, OraFine = ?, Costo = ?, TempoPausa = ?, Descrizione = ?, IsActive = ?, BookableUntil = ? WHERE id = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        print($isActive);
        if ($isActive) {
            print("true");
            $isActive = 1;
        } else {
            print("false");
            $isActive = 0;
        }
        print($isActive);
        if (!$stmt->bind_param('sissiisiii', $name, $duration, $startTime, $endTime, $cost, $waitTime, $description, $isActive, $bookableUntil, $serviceId)) {
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

    // remove a service from db
    public static function deleteService($id) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'DELETE FROM Servizio WHERE Servizio.id = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $id)) {
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

    // get the list of employees that can be added to a service
    public static function getEmployeesStatusForService($serviceId, $name){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $employeesActive = \Admin\Services::getEmployeeList($serviceId);
        $db = Database::getDB();
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente WHERE (CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) LIKE ?) GROUP BY Dipendente.id';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        $name = "%$name%";
        if (!$stmt->bind_param('s', $name)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $employees = array();
            $result = $stmt->get_result();
            foreach ($result as $r) {
                $isFound = false;
                foreach ($employeesActive as $activeEmployees){
                    if ($r['id'] == $activeEmployees['id']){
                        $employees[] = array("id" =>$r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'], "available_action" => "delete");
                        $isFound = true;
                        break;
                    }
                }
                if (!$isFound){
                    $employees[] = array("id" =>$r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'], "available_action" => "add");
                }
            }
            return $employees;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        return false;
    }

    public static function addEmployeeToService($serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'INSERT INTO Offre (Dipendente_id, Servizio_id) VALUES (?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('ii', $employeeId, $serviceId)) {
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

    public static function removeEmployeeToService($serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $db = Database::getDB();
        $sql = 'DELETE FROM Offre WHERE Offre.Dipendente_id = ? AND Offre.Servizio_id = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('ii', $employeeId, $serviceId)) {
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