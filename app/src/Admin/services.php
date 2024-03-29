<?php

namespace Admin;

use Database;
use DatabaseException;
use Service;

class Services {
    /**
     * @param Database $db
     * @param $isAdmin
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the list of services associated to an employee based on its id, if the user is an admin it gets all the
     * active services, it doesn't check if the employee is active
     */
    public static function getEmployeeService(Database $db, $isAdmin, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            return Service::getActiveServices($db);
        } else {
            $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.Costo FROM Servizio, Offre, Dipendente WHERE(Servizio.IsActive = TRUE AND Dipendente.IsActive = TRUE AND Servizio.id = Offre.Servizio_id AND Offre.Dipendente_id = Dipendente.id AND Dipendente.id = ?) GROUP BY Servizio.id";
            $status = $db->query($sql, "i", $employeeId);
            if (!$status) {
                return [];
            }
            //Success
            $result = $db->getResult();
            $response = [];
            foreach ($result as $r) {
                $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'], 'Costo' => $r['Costo']);
            }
            return $response;
        }
    }

    /**
     * @param Database $db
     * @param $name
     * @param $duration
     * @param $startTime
     * @param $endTime
     * @param $cost
     * @param $waitTime
     * @param $bookableUntil
     * @param $isActive
     * @param string $description
     * @return bool
     * @throws DatabaseException Add a service to the database
     */
    public static function addServices(Database $db, $name, $duration, $startTime, $endTime, $cost, $waitTime,
                                       $bookableUntil, $isActive, $description = ""): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Servizio (id, Nome, Durata, Costo, TempoPausa, Descrizione, ImmagineUrl, IsActive, BookableUntil) VALUES (NULL, ?, ?, ?, ?, ?, "", ?, ?)';
        if ($isActive) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        $status = $db->query($sql, "siiisii", $name, $duration, $cost, $waitTime, $description, $isActive, $bookableUntil);
        if ($status) {
            //Success, proceed with adding the working times
            $serviceId = $db->getInsertId();
            $sql = "INSERT INTO OrariServizio (idOrariServizio, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Servizio_id, isCustom, StartDate, EndDate) VALUES (NULL, 1, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 2, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 3, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 4, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 5, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 6, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 7, ?, ?, ?, ?, ?, 0, NULL, NULL)";
            $status = $db->query($sql, "ssississississississssi", $startTime, $endTime, $serviceId, $startTime, $endTime, $serviceId, $startTime, $endTime, $serviceId, $startTime, $endTime, $serviceId, $startTime, $endTime, $serviceId, $startTime, $endTime, $serviceId, "00:00", "00:00", "00:00", "23:59", $serviceId,);
            if (!$status) {
                self::deleteService($db, $serviceId);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param int|null $id
     * @return bool|array
     * @throws DatabaseException
     * Gets the list of services for the admin services list page
     * It retrieves from the database all service info, including the number of active employee that are associated to
     * the service
     */
    public static function getServiceList(Database $db, int $id = null): bool|array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null) {
            $sql = 'SELECT id, Nome, Durata, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre, Dipendente WHERE (Offre.Servizio_id=Servizio.id AND Dipendente.IsActive = TRUE AND Offre.Dipendente_id = Dipendente.id)) AS NumDipendenti FROM Servizio';
        } else {
            $sql = 'SELECT id, Nome, Durata, Costo, TempoPausa, Descrizione, IsActive, BookableUntil, (SELECT COUNT(*) FROM Offre, Dipendente WHERE (Offre.Servizio_id=Servizio.id AND Dipendente.IsActive = TRUE AND Offre.Dipendente_id = Dipendente.id)) AS NumDipendenti FROM Servizio WHERE Servizio.id = ?';
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
                $services[] = array("id" => $r['id'], "name" => $r['Nome'], "duration" => $r['Durata'], "cost" => $r['Costo'],
                    "waitTime" => $r['TempoPausa'], "description" => $r['Descrizione'], "isActive" => $r['IsActive'],
                    "bookableUntil" => $r['BookableUntil'], "employeesNumber" => $r['NumDipendenti']);
            }
            return $services;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $serviceId
     * @return bool|array
     * @throws DatabaseException
     * Gets the list of active employees that are associated to the service
     */
    public static function getActiveEmployeeList(Database $db, $serviceId): bool|array {
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

    /**
     * @param Database $db
     * @param Service $service
     * @return bool
     * @throws DatabaseException
     * @throws \ServiceException
     * Update service information
     */
    public static function updateService(Database $db, Service $service): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'UPDATE Servizio SET Nome = ?, Durata = ?, Costo = ?, TempoPausa = ?, Descrizione = ?, IsActive = ?, BookableUntil = ? WHERE id = ?';
        if ($service->getIsActive()) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        $status = $db->query($sql, "siiisiii", $service->getName(), $service->getDuration(), $service->getCost(),
            $service->getWaitTime(), $service->getDescription(), $isActive, $service->getBookableUntil(), $service->getServiceId());
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $id
     * @return bool
     * @throws DatabaseException
     * Delete a service from database
     */
    public static function deleteService(Database $db, $id): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'DELETE FROM Servizio WHERE Servizio.id = ?';
        $status = $db->query($sql, "i", $id);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $serviceId
     * @param $name
     * @return bool|array
     * @throws DatabaseException
     * Return a list of active employees with a field that allows to check if an employee can be added to a service or removed
     * It also performs a search with a name parameter
     */
    public static function getEmployeesStatusForService(Database $db, $serviceId, $name): bool|array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $employeesActive = \Admin\Services::getActiveEmployeeList($db, $serviceId);
        $sql = 'SELECT Dipendente.id, Dipendente.Nome, Dipendente.Cognome FROM Dipendente WHERE (CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) LIKE ? AND Dipendente.isActive = TRUE) GROUP BY Dipendente.id ORDER BY Dipendente.Nome';
        $name = "%$name%";
        $status = $db->query($sql, "s", $name);
        if (!$status) {
            return false;
        }
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

    /**
     * @param Database $db
     * @param $serviceId
     * @param $employeeId
     * @return bool
     * @throws DatabaseException
     * Allows to add an employee to a service
     */
    public static function addEmployeeToService(Database $db, $serviceId, $employeeId): bool {
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
     * @throws DatabaseException
     * @return bool
     * Remove an employee from a service, it keeps all the previous booking
     */
    public static function removeEmployeeToService(Database $db, $serviceId, $employeeId): bool {
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
     * @param Database $db
     * @param $serviceId
     * @return array
     * @throws DatabaseException
     * Gets service working times given a service id
     */
    public static function getWorkingTimes(Database $db, $serviceId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT idOrariServizio, GiornoSettimana, TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro, TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa, IsCustom, DATE_FORMAT(StartDate, "%e/%m/%Y") AS StartDate, DATE_FORMAT(EndDate, "%e/%m/%Y") AS EndDate FROM OrariServizio WHERE (Servizio_id = ? AND (EndDate >= CURRENT_DATE() OR EndDate IS NULL))';
        $status = $db->query($sql, "i", $serviceId);
        if ($status) {
            // Success
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
                    $standardTime[] = ["day" => $r['GiornoSettimana'], "workStartTime" => $r["InizioLavoro"],
                        "workEndTime" => $r['FineLavoro'], "breakStartTime" => $r['InizioPausa'], "breakEndTime" => $r['FinePausa']];
                } else {
                    if ($r['StartDate'] == null) {
                        $r['StartDate'] = "";
                    }
                    if ($r['EndDate'] == null) {
                        $r['EndDate'] = "";
                    }
                    $customTime[] = ["timeId" => $r["idOrariServizio"], "startDate" => $r["StartDate"], "endDate" => $r["EndDate"],
                        "workStartTime" => $r["InizioLavoro"], "workEndTime" => $r['FineLavoro'], "breakStartTime" => $r['InizioPausa'],
                        "breakEndTime" => $r['FinePausa']];
                }
            }
            return ["standard" => $standardTime, "custom" => $customTime];
        } else {
            return [];
        }
    }

    /**
     * @param Database $db
     * @param $data
     * @return array|bool
     * @throws DatabaseException
     * Adds working time to a service
     */
    public static function addWorkingTimes(Database $db, $data): array|bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $status = false;
        // Custom working time
        if ($data->timeType === "custom") {
            // check if there are collisions
            if (self::checkCollisionsInWorkTimes($db, $data->startDay, $data->endDay, $data->serviceId)){
                return ["warning" => "conflict"];
            }
            $sql = 'INSERT INTO OrariServizio (idOrariServizio, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Servizio_id, isCustom, StartDate, EndDate) VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)';
            if ($data->freeDay) {
                $data->startTime = "00:00";
                $data->endTime = "00:00";
                $data->startBreak = "00:00";
                $data->endBreak = "23:59";
            }
            $data->startTime = ($data->startTime === "") ? "08:00" : $data->startTime;
            $data->endTime = ($data->endTime === "") ? "17:00" : $data->endTime;
            $data->startBreak = ($data->startBreak === "") ? null : $data->startBreak;
            $data->endBreak = ($data->endBreak === "") ? null : $data->endBreak;
            // validate input if freeday isn't set
            if ($data->freeDay || (is_null($data->startBreak) && is_null($data->endBreak) &&
                    $data->endTime > $data->startTime && $data->startDay <= $data->endDay) ||
                ($data->endTime > $data->startTime && $data->startBreak > $data->startTime &&
                    $data->startBreak < $data->endBreak && $data->endBreak > $data->startBreak &&
                    $data->endBreak < $data->endTime && $data->startDay <= $data->endDay)) {
                // Input is valid
                $status = $db->query($sql, "ssssiiss", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->serviceId, 1, $data->startDay, $data->endDay);
                if ($status && $db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            // Not custom working times
            $sql = 'UPDATE OrariServizio SET InizioLavoro = ?, FineLavoro = ?, InizioPausa = ?, FinePausa = ? WHERE(Servizio_id = ? AND GiornoSettimana = ?)';
            if ($data->freeDay) {
                $data->startTime = "00:00";
                $data->endTime = "00:00";
                $data->startBreak = "00:00";
                $data->endBreak = "23:59";
            }
            $data->startTime = ($data->startTime === "") ? "08:00" : $data->startTime;
            $data->endTime = ($data->endTime === "") ? "17:00" : $data->endTime;
            $data->startBreak = ($data->startBreak === "") ? null : $data->startBreak;
            $data->endBreak = ($data->endBreak === "") ? null : $data->endBreak;
            $days = $data->days;
            foreach ($days as $day) {
                // validate input if freeday isn't set
                if ($data->freeDay || (is_null($data->startBreak) && is_null($data->endBreak) && $data->endTime > $data->startTime) ||
                    ($data->endTime > $data->startTime && $data->startBreak > $data->startTime &&
                        $data->startBreak < $data->endBreak && $data->endBreak > $data->startBreak && $data->endBreak < $data->endTime)) {
                    $status = $db->query($sql, "ssssii", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->serviceId, $day);
                    if (!$status) {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            if ($status) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param Database $db
     * @param $id
     * @return bool
     * @throws DatabaseException
     * Delete a custom working time given its id
     */
    public static function deleteCustomWorkTime(Database $db, $id): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM OrariServizio WHERE (idOrariServizio = ? AND isCustom = 1)";
        $status = $db->query($sql, "i", $id);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Database $db
     * @param $day
     * @param $serviceId
     * @return array
     * @throws DatabaseException
     * Gets the currents worktimes for a service given a day of the week and the service identifier
     */
    public static function getDayWorkingTimes(Database $db, $day, $serviceId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro FROM OrariServizio WHERE(isCustom = 0 AND GiornoSettimana = ? AND Servizio_id = ?) LIMIT 1';
        $status = $db->query($sql, "ii", $day, $serviceId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            //Success
            $r = $result[0];
            if (is_null($r['InizioLavoro']) || is_null($r['FineLavoro'])) {
                return ['startTime' => "00:00", 'endTime' => "00:00"];
            }
            return array('startTime' => $r['InizioLavoro'], 'endTime' => $r['FineLavoro']);
        }
        return [];
    }

    /**
     * @param Database $db
     * @param $date
     * @param $serviceId
     * @return array
     * @throws DatabaseException
     * Gets the currents custom worktimes for a service given a date string and the service identifier
     */
    public static function getDayCustomWorkingTimes(Database $db, $date, $serviceId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro FROM OrariServizio WHERE(isCustom = 1 AND StartDate <= ? AND EndDate >= ? AND Servizio_id = ?) LIMIT 1';
        $status = $db->query($sql, "ssi", $date, $date, $serviceId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            //Success
            $r = $result[0];
            if (is_null($r['InizioLavoro']) || is_null($r['FineLavoro'])) {
                return ['startTime' => "00:00", 'endTime' => "00:00"];
            }
            return array('startTime' => $r['InizioLavoro'], 'endTime' => $r['FineLavoro']);
        }
        return [];
    }

    /**
     * @param Database $db
     * @param $day
     * @param $serviceId
     * @return array
     * @throws DatabaseException
     * Gets the currents holiday times for a service given a day of the week and the service identifier
     */
    public static function getDayHolidayTimes(Database $db, $day, $serviceId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa FROM OrariServizio WHERE(isCustom = 0 AND GiornoSettimana = ? AND Servizio_id = ?) LIMIT 1';
        $status = $db->query($sql, "ii", $day, $serviceId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            //Success
            $r = $result[0];
            if (is_null($r['InizioPausa']) || is_null($r['FinePausa'])) {
                return ['startTime' => "00:00", 'endTime' => "00:00"];
            }
            return array('startTime' => $r['InizioPausa'], 'endTime' => $r['FinePausa']);
        }
        return [];
    }

    /**
     * @param Database $db
     * @param $date
     * @param $serviceId
     * @return array
     * @throws DatabaseException
     * Gets the currents custom holiday times for a service given a date string and the service identifier
     */
    public static function getDayCustomHolidayTimes(Database $db, $date, $serviceId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa FROM OrariServizio WHERE(isCustom = 1 AND StartDate <= ? AND EndDate >= ? AND Servizio_id = ?) LIMIT 1';
        $status = $db->query($sql, "ssi", $date, $date, $serviceId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            //Success
            $r = $result[0];
            if (is_null($r['InizioPausa']) || is_null($r['FinePausa'])) {
                return ['startTime' => "00:00", 'endTime' => "00:00"];
            }
            return array('startTime' => $r['InizioPausa'], 'endTime' => $r['FinePausa']);
        }
        return [];
    }

    /**
     * @param Database $db
     * @param $startDate
     * @param $endDate
     * @param $serviceId
     * @return bool
     * @throws DatabaseException
     * Checks if there is a collision in worktimes for a specific service. A collision made when the user try to create 2
     * custom work times
     */
    public static function checkCollisionsInWorkTimes(Database $db, $startDate, $endDate, $serviceId): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT idOrariServizio FROM OrariServizio WHERE (isCustom = 1 AND NOT ((? < StartDate AND ? < StartDate) OR (? > EndDate AND ? >= ?)) AND ? <= ? AND Servizio_id = ?) LIMIT 5";
        $status = $db->query($sql, "sssssssi", $startDate, $endDate, $startDate, $endDate, $startDate, $startDate, $endDate, $serviceId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() > 0) {
            //Success
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $data
     * @return bool|array
     * @throws DatabaseException
     * If we already have a custom worktime set for a set of date or a single date, we need to remove it first, or remove
     * all custom worktimes associated to the new worktime start and end date. Only after we have removed all worktimes
     * that are in conflict between them we can add the new one
     */
    public static function overrideCustomWorkTimes(Database $db, $data): bool|array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM OrariServizio WHERE (isCustom = 1 AND NOT ((? < StartDate AND ? < StartDate) OR (? > EndDate AND ? >= ?)) AND ? <= ? AND Servizio_id = ?)";
        $status = $db->query($sql, "sssssssi", $data->startDay, $data->endDay, $data->startDay, $data->endDay, $data->startDay, $data->startDay, $data->endDay, $data->serviceId);
        if ($status && $db->getAffectedRows() > 0) {
            //Success
            return self::addWorkingTimes($db, $data);
        }
        return false;
    }
}