<?php

namespace Admin;

use Database;
use DatabaseException;
use Service;

class Services {
    /**
     * @throws DatabaseException
     */
    // gets the service that an employee is offering
    public static function getEmployeeService(Database $db, $isAdmin, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $sql = "SELECT id, Nome, Durata, OraInizio, OraFine, Costo FROM Servizio WHERE(IsActive = TRUE)";
            $status = $db->query($sql);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            }
        } else {
            $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.OraInizio, Servizio.OraFine, Servizio.Costo FROM Servizio, Offre WHERE (Offre.Dipendente_id = ? AND Offre.Servizio_id = Servizio.id)";
            $status = $db->query($sql, "i", $employeeId);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            }
        }
        return [];
    }

    // Add a service
    public static function addServices(Database $db, $name, $duration, $startTime, $endTime, $cost, $waitTime, $bookableUntil, $isActive, $description = "") {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Servizio (id, Nome, Durata, OraInizio, OraFine, Costo, TempoPausa, Descrizione, ImmagineUrl, IsActive, BookableUntil) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, NULL, ?, ?) ';
        if ($isActive) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        $status = $db->query($sql, "sissiisii", $name, $duration, $startTime, $endTime, $cost, $waitTime, $description, $isActive, $bookableUntil);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    // Get the list of services

    /**
     * @throws DatabaseException
     */
    public static function getServiceList(Database $db, $id = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null) {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre WHERE Offre.Servizio_id=Servizio.id) AS NumDipendenti FROM Servizio';
        } else {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre WHERE Offre.Servizio_id=Servizio.id) AS NumDipendenti FROM Servizio WHERE Servizio.id = ?';
        }
        if ($id != null) {
            $status = $db->query($sql, "i", $id);
        } else {
            $status = $db->query($sql);
        }
        if ($status) {
            //Success
            $services = [];
            $result = $db->getResult();
            foreach ($result as $r) {
                $services[] = array("id" => $r['id'], "name" => $r['Nome'], "duration" => $r['Durata'], "startTime" => $r['OraInizio'],
                    "endTime" => $r['OraFine'], "cost" => $r['Costo'], "waitTime" => $r['TempoPausa'], "description" => $r['Descrizione'],
                    "isActive" => $r['IsActive'], "bookableUntil" => $r['BookableUntil'], "employeesNumber" => $r['NumDipendenti']);
            }
            return $services;
        }
        return false;
    }

    // Get the list of employees that offer a service

    /**
     * @throws DatabaseException
     */
    public static function getEmployeeList(Database $db, $serviceId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente, Offre  WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
        $status = $db->query($sql, "i", $serviceId);
        if ($status) {
            //Success
            $employees = [];
            $result = $db->getResult();
            foreach ($result as $r) {
                $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome']);
            }
            return $employees;
        }
        return false;
    }

    // update a service

    /**
     * @throws DatabaseException
     */
    public static function updateService(Database $db, Service $service ) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'UPDATE Servizio SET Nome = ?, Durata = ?, OraInizio = ?, OraFine = ?, Costo = ?, TempoPausa = ?, Descrizione = ?, IsActive = ?, BookableUntil = ? WHERE id = ?';
        if ($service->getIsActive()) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        $status = $db->query($sql, "sissiisiii", $service->getName(), $service->getDuration(), $service->getStartTime(),
            $service->getEndTime(), $service->getCost(), $service->getWaitTime(), $service->getDescription(), $isActive,
            $service->getBookableUntil(), $service->getServiceId());
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    // remove a service from db

    /**
     * @throws DatabaseException
     */
    public static function deleteService(Database $db, $id) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'DELETE FROM Servizio WHERE Servizio.id = ?';
        $status = $db->query($sql, "i", $id);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    // get the list of employees that can be added to a service

    /**
     * @throws DatabaseException
     */
    public static function getEmployeesStatusForService(Database $db, $serviceId, $name){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $employeesActive = \Admin\Services::getEmployeeList($db, $serviceId);
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente WHERE (CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) LIKE ?) GROUP BY Dipendente.id ORDER BY Dipendente.Nome';
        $name = "%$name%";
        $status = $db->query($sql, "s", $name);
        if ($status) {
            //Success
            $employees = [];
            $result = $db->getResult();
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
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function addEmployeeToService(Database $db, $serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Offre (Dipendente_id, Servizio_id) VALUES (?, ?)';
        $status = $db->query($sql, "ii", $employeeId, $serviceId);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    public static function removeEmployeeToService($db, $serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'DELETE FROM Offre WHERE Offre.Dipendente_id = ? AND Offre.Servizio_id = ?';
        $status = $db->query($sql, "ii", $employeeId, $serviceId);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }
}