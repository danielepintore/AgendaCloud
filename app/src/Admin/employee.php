<?php
namespace Admin;
use Database;
use DatabaseException;

class Employee {
    /**
     * @throws DatabaseException
     */
    public static function getEmployees(Database $db, $id = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null){
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
    public static function addEmployee(Database $db, $name, $surname, $role, $username, $password, $admin, $isActive){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Dipendente (id, Nome, Cognome, Ruolo, Username, Password, UserType, isActive) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)';
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if ($isActive){
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
    public static function updateEmployee(Database $db, $id, $name, $surname, $role, $username, $password, $admin, $isActive){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($password)){
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, UserType = ?, isActive = ? WHERE (id = ?)';
        } else {
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, Password = ?, UserType = ?, isActive = ? WHERE (id = ?)';
        }
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if (empty($password)){
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
    public static function deleteEmployee(Database $db, $employeId, $loggedId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($loggedId != $employeId){
            $sql = 'DELETE FROM Dipendente WHERE Dipendente.id = ?';
            $status = $db->query($sql, "i", $employeId);
            if ($status) {
                //Success
                return true;
            } else {
                return false;
            }
        }
        throw DatabaseException::cantDeleteCurrentUser();
    }
}