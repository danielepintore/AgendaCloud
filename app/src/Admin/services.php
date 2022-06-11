<?php

namespace Admin;

use Database;
use DatabaseException;
use Service;

class Services {
    /**
     * @return array
     * @throws DatabaseException
     * Gets the list of services associated to an employee based on it's id, if the user is an admin it gets all the
     * active services, it doesn't check if the employee is active
     */
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

    /**
     * @return bool
     * @throws DatabaseException
     * Add a service to the database
     */
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
     * @return array
     * @throws DatabaseException
     * Gets the list of services for the admin services list page
     * It's retrive from the database all service info, including the number of active employee that are associated to
     * the service
     */
    public static function getServiceList(Database $db, $id = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null) {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre, Dipendente WHERE (Offre.Servizio_id=Servizio.id AND Dipendente.IsActive = TRUE AND Offre.Dipendente_id = Dipendente.id)) AS NumDipendenti FROM Servizio';
        } else {
            $sql = 'SELECT id, Nome, Durata, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre, Dipendente WHERE (Offre.Servizio_id=Servizio.id AND Dipendente.IsActive = TRUE AND Offre.Dipendente_id = Dipendente.id)) AS NumDipendenti FROM Servizio WHERE Servizio.id = ?';
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

    /**
     * @throws DatabaseException
     * Gets the list of active employees that are associated to the service
     */
    public static function getActiveEmployeeList(Database $db, $serviceId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente, Offre  WHERE (Dipendente.isActive = TRUE AND Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
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
     * Update service information
     */
    public static function updateService(Database $db, Service $service) {
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
     * Delete a service from database
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
     * Return a list of active employees with a field that allows to check if an employee can be added to a service or removed
     * Also it performs a search with a name parameter
     */
    public static function getEmployeesStatusForService(Database $db, $serviceId, $name) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $employeesActive = \Admin\Services::getActiveEmployeeList($db, $serviceId);
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente WHERE (CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) LIKE ? AND Dipendente.isActive = TRUE) GROUP BY Dipendente.id ORDER BY Dipendente.Nome';
        $name = "%$name%";
        $status = $db->query($sql, "s", $name);
        if ($status) {
            //Success
            $employees = [];
            $result = $db->getResult();
            foreach ($result as $r) {
                $isFound = false;
                foreach ($employeesActive as $activeEmployees) {
                    if ($r['id'] == $activeEmployees['id']) {
                        $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'], "available_action" => "delete");
                        $isFound = true;
                        break;
                    }
                }
                if (!$isFound) {
                    $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'], "available_action" => "add");
                }
            }
            return $employees;
        }
        return false;
    }

    /**
     * @throws DatabaseException
     * Allows to add an employee to a service
     */
    public static function addEmployeeToService(Database $db, $serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (Employee::isActive($db, $employeeId)) {
            $sql = 'INSERT INTO Offre (Dipendente_id, Servizio_id) VALUES (?, ?)';
            $status = $db->query($sql, "ii", $employeeId, $serviceId);
            if ($status) {
                //Success
                return true;
            }
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $serviceId
     * @param $employeeId
     * @return bool
     * Remove an employee from a service, it keeps all the previous booking
     * @throws DatabaseException
     */
    public static function removeEmployeeToService(Database $db, $serviceId, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'DELETE FROM Offre WHERE Offre.Dipendente_id = ? AND Offre.Servizio_id = ?';
        $status = $db->query($sql, "ii", $employeeId, $serviceId);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    /**
     * @throws DatabaseException
     * Gets an array containing all holidays for a specifc service with a limit of 10 entries per query
     */
    public static function searchHolidays(Database $db, $serviceId, $date) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT id, DATE_FORMAT(Data, "%e/%m/%Y") AS Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoChiusuraServizio WHERE (Servizio_id = ? AND Data LIKE ? AND Data >= CURDATE()) LIMIT 10';
        $status = $db->query($sql, "is", $serviceId, "%$date%");
        if ($status) {
            $result = $db->getResult();
            $holidays = [];
            foreach ($result as $r) {
                $holidays[] = ["id" => $r['id'], "date" => $r["Data"], "startTime" => $r['OraInizio'], "endTime" => $r['OraFine']];
            }
            return $holidays;
        } else {
            return [];
        }
    }

    /**
     * @throws DatabaseException
     * Add a holiday to a service
     */
    public static function addHoliday(Database $db, $serviceId, $date, $startTime, $endTime) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "INSERT INTO GiornoChiusuraServizio (Data, OraInizio, OraFine, Servizio_id) VALUES (?, ?, ?, ?)";
        $status = $db->query($sql, "sssi", $date, $startTime, $endTime, $serviceId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function deleteHoliday(Database $db, $holidayId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM GiornoChiusuraServizio WHERE GiornoChiusuraServizio.id = ?";
        $status = $db->query($sql, "i", $holidayId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function getServiceWorkingTimes(Database $db, $serviceId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT idOrariServizio, GiornoSettimana, TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro, TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa, IsCustom, DATE_FORMAT(StartDate, "%e/%m/%Y") AS StartDate, DATE_FORMAT(EndDate, "%e/%m/%Y") AS EndDate FROM OrariServizio WHERE (Servizio_id = ? AND (EndDate >= CURRENT_DATE() OR EndDate IS NULL))';
        $status = $db->query($sql, "i", $serviceId);
        if ($status) {
            $result = $db->getResult();
            $standardTime = [];
            $customTime = [];
            foreach ($result as $r) {
                if ($r['InizioPausa'] == null) {
                    $r['InizioPausa'] = "";
                }
                if ($r['FinePausa'] == null) {
                    $r['FinePausa'] = "";
                }
                if ($r['IsCustom'] == 0) {
                    $standardTime[] = ["day" => $r['GiornoSettimana'], "workStartTime" => $r["InizioLavoro"], "workEndTime" => $r['FineLavoro'], "breakStartTime" => $r['InizioPausa'], "breakEndTime" => $r['FinePausa']];
                } else {
                    if ($r['StartDate'] == null) {
                        $r['StartDate'] = "";
                    }
                    if ($r['EndDate'] == null) {
                        $r['EndDate'] = "";
                    }
                    $customTime[] = ["timeId" => $r["idOrariServizio"], "startDate" => $r["StartDate"], "endDate" => $r["EndDate"], "workStartTime" => $r["InizioLavoro"], "workEndTime" => $r['FineLavoro'], "breakStartTime" => $r['InizioPausa'], "breakEndTime" => $r['FinePausa']];
                }
            }
            return ["standard" => $standardTime, "custom" => $customTime];
        } else {
            return [];
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function updateWorkingTimes(Database $db, $data){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($data->timeType === "custom") {
            $sql = 'INSERT INTO OrariServizio (idOrariServizio, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Servizio_id, isCustom, StartDate, EndDate) VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)';
            if ($data->freeDay) {
                $data->startTime = "00:00";
                $data->endTime = "00:00";
                $data->startBreak = "00:00";
                $data->endBreak = "24:00";
            }
            $data->startTime = ($data->startTime === "") ? "08:00" : $data->startTime;
            $data->endTime = ($data->endTime === "") ? "17:00" : $data->endTime;
            $data->startBreak = ($data->startBreak === "") ? null : $data->startBreak;
            $data->endBreak = ($data->endBreak === "") ? null : $data->endBreak;
            // validate input if freeday isn't set
            if ($data->freeDay || ($data->endTime > $data->startTime && $data->startBreak > $data->startTime && $data->startBreak < $data->endBreak && $data->endBreak > $data->startBreak && $data->endBreak < $data->endTime)){
                $status = $db->query($sql, "ssssiiss", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, 1, $data->startDay, $data->endDay);
            } else {
                return false;
            }
        } else {
            $sql = 'UPDATE OrariServizio SET InizioLavoro = ?, FineLavoro = ?, InizioPausa = ?, FinePausa = ? WHERE(Servizio_id = ? AND GiornoSettimana = ?)';
            if ($data->freeDay) {
                $data->startTime = "00:00";
                $data->endTime = "00:00";
                $data->startBreak = "00:00";
                $data->endBreak = "24:00";
            }
            $data->startTime = ($data->startTime === "") ? "08:00" : $data->startTime;
            $data->endTime = ($data->endTime === "") ? "17:00" : $data->endTime;
            $data->startBreak = ($data->startBreak === "") ? null : $data->startBreak;
            $data->endBreak = ($data->endBreak === "") ? null : $data->endBreak;
            $days = $data->days;
            foreach ($days as $day) {
                // validate input if freeday isn't set
                if ($data->freeDay || ($data->endTime > $data->startTime && $data->startBreak > $data->startTime && $data->startBreak < $data->endBreak && $data->endBreak > $data->startBreak && $data->endBreak < $data->endTime)){
                    $status = $db->query($sql, "ssssii", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, $day);
                } else {
                    return false;
                }
                if (!$status) {
                    return false;
                }
            }
        }
        if ($status) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function deleteCustomWorkTime(Database $db, $id){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM OrariServizio WHERE (idOrariServizio = ? AND isCustom = 1)";
        $status = $db->query($sql, "i", $id);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }
}