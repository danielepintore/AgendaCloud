<?php

namespace Admin;

use Database;
use DatabaseException;
use PHPMailer\PHPMailer\Exception;

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

    /**
     * @throws DatabaseException
     */
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

    /**
     * @throws DatabaseException
     */
    public static function getEmployeeWorkingTimes(Database $db, $employeeId) {
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
     * @throws DatabaseException
     */
    public static function updateWorkingTimes(Database $db, $data) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($data->timeType === "custom") {
            $sql = 'INSERT INTO OrariDipendente (idOrariDipendente, GiornoSettimana, InizioLavoro, FineLavoro, InizioPausa, FinePausa, Dipendente_id, isCustom, StartDate, EndDate) VALUES (NULL, NULL, ?, ?, ?, ?, ?, ?, ?, ?)';
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
            $status = $db->query($sql, "ssssiiss", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, 1, $data->startDay, $data->endDay);
        } else {
            $sql = 'UPDATE OrariDipendente SET InizioLavoro = ?, FineLavoro = ?, InizioPausa = ?, FinePausa = ? WHERE(Dipendente_id = ? AND GiornoSettimana = ?)';
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
                $status = $db->query($sql, "ssssii", $data->startTime, $data->endTime, $data->startBreak, $data->endBreak, $data->userId, $day);
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
        $sql = "DELETE FROM OrariDipendente WHERE (idOrariDipendente = ? AND isCustom = 1)";
        $status = $db->query($sql, "i", $id);
        if ($status && $db->getAffectedRows() == 1) {
            return true;
        } else {
            return false;
        }
    }
}