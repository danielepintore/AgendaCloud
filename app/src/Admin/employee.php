<?php

namespace Admin;

use Database;
use DatabaseException;

class Employee {
    /**
     * @throws DatabaseException
     */
    public static function getEmployees(Database $db, $id = null) {
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
     * @throws DatabaseException
     */
    public static function addEmployee(Database $db, $name, $surname, $role, $username, $password, $admin, $isActive) {
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
            //Success
            return true;
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function updateEmployee(Database $db, $id, $name, $surname, $role, $username, $password, $admin, $isActive) {
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
     * @throws DatabaseException
     */
    public static function deleteEmployee(Database $db, $employeeId, $loggedId) {
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
     * @throws DatabaseException
     * Return true if the user is active otherwise false
     */
    public static function isActive(Database $db, $employeeId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT isActive FROM Dipendente WHERE Dipendente.id = ?';
        $status = $db->query($sql, "i", $employeeId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() == 1) {
            if ($result[0]['isActive']){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @throws DatabaseException
     * Gets the holidays for an employee, it also performs a search based on date parameter.
     * It outputs formatted data
     */
    public static function searchHolidays(Database $db, $userId, $date) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT id, DATE_FORMAT(Data, "%e/%m/%Y") AS Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoLiberoDipendente WHERE (Dipendente_id = ? AND Data LIKE ? AND Data >= CURDATE()) LIMIT 10';
        $status = $db->query($sql, "is", $userId, "%$date%");
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
    public static function addHoliday(Database $db, $employeeId, $date, $startTime, $endTime) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "INSERT INTO GiornoLiberoDipendente (Data, OraInizio, OraFine, Dipendente_id) VALUES (?, ?, ?, ?)";
        $status = $db->query($sql, "sssi", $date, $startTime, $endTime, $employeeId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteHoliday(Database $db, $holidayId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "DELETE FROM GiornoLiberoDipendente WHERE GiornoLiberoDipendente.id = ?";
        $status = $db->query($sql, "i", $holidayId);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }
}