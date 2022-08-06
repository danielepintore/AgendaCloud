<?php

namespace Admin;

use Database;
use DatabaseException;
use PHPMailer\PHPMailer\Exception;

class Employee {
    /**
     * @param Database $db
     * @param null $id
     * @return bool|array
     * @throws DatabaseException
     * Gets the info of an employee (if it's specified its id) or the info of all employees
     */
    public static function getEmployees(Database $db, $id = null): bool|array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null) {
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType, isActive FROM Dipendente';
            $status = $db->query($sql);
        } else {
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType, isActive FROM Dipendente WHERE id = ?';
            $status = $db->query($sql, "i", $id);
        }
        if ($status) {
            //Success
            $employees = [];
            $result = $db->getResult();
            foreach ($result as $r) {
                $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'],
                    "role" => $r['Ruolo'], "username" => $r['Username'], "userType" => $r['UserType'],
                    "isActive" => $r['isActive']);
            }
            return $employees;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $name
     * @param $surname
     * @param $role
     * @param $username
     * @param $password
     * @param $admin
     * @param $isActive
     * @return bool
     * @throws DatabaseException
     * Adds an employee, return true on success otherwise false
     */
    public static function addEmployee(Database $db, $name, $surname, $role, $username, $password, $admin, $isActive): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Dipendente (id, Nome, Cognome, Ruolo, Username, Password, UserType, isActive) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)';
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if ($isActive) {
            $isActive = 1;
        } else {
            $isActive = 0;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $status = $db->query($sql, "sssssii", $name, $surname, $role, $username, $password, $admin, $isActive);
        if ($status) {
            //Success, proceed with adding the working times
            $employeeId = $db->getInsertId();
            $startTime = "08:00";
            $endTime = "18:00";
            $sql = "INSERT INTO OrariDipendente (idOrariDipendente, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Dipendente_id, isCustom, StartDate, EndDate) VALUES (NULL, 1, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 2, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 3, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 4, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 5, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 6, ?, ?, NULL, NULL, ?, 0, NULL, NULL), (NULL, 7, ?, ?, ?, ?, ?, 0, NULL, NULL)";
            $status = $db->query($sql, "ssississississississssi", $startTime, $endTime, $employeeId, $startTime, $endTime, $employeeId, $startTime, $endTime, $employeeId, $startTime, $endTime, $employeeId, $startTime, $endTime, $employeeId, $startTime, $endTime, $employeeId, "00:00", "00:00", "00:00", "23:59", $employeeId,);
            if (!$status) {
                self::deleteEmployee($db, $employeeId, -1);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $id
     * @param $name
     * @param $surname
     * @param $role
     * @param $username
     * @param $password
     * @param $admin
     * @param $isActive
     * @return bool
     * @throws DatabaseException
     * Given the id of an employer edit its information
     */
    public static function updateEmployee(Database $db, $id, $name, $surname, $role, $username, $password, $admin, $isActive): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($password)) {
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, UserType = ?, isActive = ? WHERE (id = ?)';
        } else {
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, Password = ?, UserType = ?, isActive = ? WHERE (id = ?)';
        }
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if (empty($password)) {
            $status = $db->query($sql, "ssssiii", $name, $surname, $role, $username, $admin, $isActive, $id);
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $status = $db->query($sql, "sssssiii", $name, $surname, $role, $username, $password, $admin, $isActive, $id);
        }
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $employeeId
     * @param $loggedId
     * @return bool
     * @throws DatabaseException
     * Delete the employee associated to that id
     */
    public static function deleteEmployee(Database $db, $employeeId, $loggedId): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($loggedId != $employeeId) {
            $sql = 'DELETE FROM Dipendente WHERE Dipendente.id = ?';
            $status = $db->query($sql, "i", $employeeId);
            if ($status) {
                //Success
                return true;
            } else {
                return false;
            }
        }
        throw DatabaseException::cantDeleteCurrentUser();
    }

    /**
     * @param Database $db
     * @param $employeeId
     * @return bool
     * @throws DatabaseException
     * Returns true if the user is active otherwise returns false
     */
    public static function isActive(Database $db, $employeeId): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT isActive FROM Dipendente WHERE Dipendente.id = ?';
        $status = $db->query($sql, "i", $employeeId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            if ($result[0]['isActive']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param Database $db
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets an array of workingtimes, returns 2 array: one containing the standard one and the other with the customs one
     */
    public static function getWorkingTimes(Database $db, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT idOrariDipendente, GiornoSettimana, TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro, TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa, IsCustom, DATE_FORMAT(StartDate, "%e/%m/%Y") AS StartDate, DATE_FORMAT(EndDate, "%e/%m/%Y") AS EndDate FROM OrariDipendente WHERE (Dipendente_id = ? AND (EndDate >= CURRENT_DATE() OR EndDate IS NULL))';
        $status = $db->query($sql, "i", $employeeId);
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
                    // the worktimes is a custom one, we need to check the dates
                    if ($r['StartDate'] == null) {
                        $r['StartDate'] = "";
                    }
                    if ($r['EndDate'] == null) {
                        $r['EndDate'] = "";
                    }
                    $customTime[] = ["timeId" => $r["idOrariDipendente"], "startDate" => $r["StartDate"], "endDate" => $r["EndDate"], "workStartTime" => $r["InizioLavoro"], "workEndTime" => $r['FineLavoro'], "breakStartTime" => $r['InizioPausa'], "breakEndTime" => $r['FinePausa']];
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
     * Adds a worktime to an employee
     */
    public static function addWorkingTimes(Database $db, $data): array|bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $status = false;
        if ($data->timeType === "custom") {
            // check if there are collisions
            if (self::checkCollisionsInWorkTimes($db, $data->startDay, $data->endDay, $data->userId)){
                return ["warning" => "conflict"];
            }
            $sql = 'INSERT INTO OrariDipendente (idOrariDipendente, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Dipendente_id, isCustom, StartDate, EndDate) VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)';
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
            if ($data->freeDay ||
                (is_null($data->startBreak) && is_null($data->endBreak) && $data->endTime > $data->startTime && $data->startDay <= $data->endDay) ||
                ($data->endTime > $data->startTime && $data->startBreak > $data->startTime && $data->startBreak < $data->endBreak &&
                    $data->endBreak > $data->startBreak && $data->endBreak < $data->endTime && $data->startDay <= $data->endDay)) {
                // input is valid
                $status = $db->query($sql, "ssssiiss", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, 1, $data->startDay, $data->endDay);
                if ($status && $db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            $sql = 'UPDATE OrariDipendente SET InizioLavoro = ?, FineLavoro = ?, InizioPausa = ?, FinePausa = ? WHERE(Dipendente_id = ? AND GiornoSettimana = ?)';
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
                    ($data->endTime > $data->startTime && $data->startBreak > $data->startTime && $data->startBreak < $data->endBreak &&
                        $data->endBreak > $data->startBreak && $data->endBreak < $data->endTime)) {
                    // Input is valid
                    $status = $db->query($sql, "ssssii", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, $day);
                    if (!$status) {
                        return false;
                    }
                } else {
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
     * @param Database $db
     * @param $id
     * @return bool
     * @throws DatabaseException
     * Given an id delete the associated custom working time
     */
    public static function deleteCustomWorkTime(Database $db, $id): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM OrariDipendente WHERE (idOrariDipendente = ? AND isCustom = 1)";
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
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the currents worktimes for an employee given a day of the week and its identifier
     */
    public static function getDayWorkingTimes(Database $db, $day, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro FROM OrariDipendente WHERE(isCustom = 0 AND GiornoSettimana = ? AND Dipendente_id = ?) LIMIT 1';
        $status = $db->query($sql, "ii", $day, $employeeId);
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
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the currents custom worktimes for an employee given a date string and its identifier
     */
    public static function getDayCustomWorkingTimes(Database $db, $date, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioLavoro, "%H:%i") AS InizioLavoro, TIME_FORMAT(FineLavoro, "%H:%i") AS FineLavoro FROM OrariDipendente WHERE(isCustom = 1 AND StartDate <= ? AND EndDate >= ? AND Dipendente_id = ?) LIMIT 1';
        $status = $db->query($sql, "ssi", $date, $date, $employeeId);
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
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the currents holiday times for an employee given a day of the week and its identifier
     */
    public static function getDayHolidayTimes(Database $db, $day, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa FROM OrariDipendente WHERE(isCustom = 0 AND GiornoSettimana = ? AND Dipendente_id = ?) LIMIT 1';
        $status = $db->query($sql, "ii", $day, $employeeId);
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
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the currents custom holiday times for an employee given a date string and its identifier
     */
    public static function getDayCustomHolidayTimes(Database $db, $date, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT TIME_FORMAT(InizioPausa, "%H:%i") AS InizioPausa, TIME_FORMAT(FinePausa, "%H:%i") AS FinePausa FROM OrariDipendente WHERE(isCustom = 1 AND StartDate <= ? AND EndDate >= ? AND Dipendente_id = ?) LIMIT 1';
        $status = $db->query($sql, "ssi", $date, $date, $employeeId);
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
     * @param $employeeId
     * @return bool
     * @throws DatabaseException
     * Checks if there is a collision in worktimes for a specific employee. A collision made when the user try to create 2
     * custom work times
     */
    public static function checkCollisionsInWorkTimes(Database $db, $startDate, $endDate, $employeeId): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT idOrariDipendente FROM OrariDipendente WHERE (isCustom = 1 AND NOT ((? < StartDate AND ? < StartDate) OR (? > EndDate AND ? >= ?)) AND ? <= ? AND Dipendente_id = ?) LIMIT 5";
        $status = $db->query($sql, "sssssssi", $startDate, $endDate, $startDate, $endDate, $startDate, $startDate, $endDate, $employeeId);
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
        $sql = "DELETE FROM OrariDipendente WHERE (isCustom = 1 AND NOT ((? < StartDate AND ? < StartDate) OR (? > EndDate AND ? >= ?)) AND ? <= ? AND Dipendente_id = ?)";
        $status = $db->query($sql, "sssssssi", $data->startDay, $data->endDay, $data->startDay, $data->endDay, $data->startDay, $data->startDay, $data->endDay, $data->userId);
        if ($status && $db->getAffectedRows() > 0) {
            //Success
            return self::addWorkingTimes($db, $data);
        }
        return false;
    }
}