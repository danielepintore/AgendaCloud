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
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType FROM Dipendente';
        } else {
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType FROM Dipendente WHERE id = ?';
        }
        $status = $db->query($sql, "i", $id);
        if ($status) {
            //Success
            $employees = [];
            $result = $db->getResult();
            foreach ($result as $r) {
                $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'],
                    "role" => $r['Ruolo'], "username" => $r['Username'], "userType" => $r['UserType']);
            }
            return $employees;
        }
        return false;
    }

    public static function addEmployee(Database $db, $name, $surname, $role, $username, $password, $admin){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Dipendente (id, Nome, Cognome, Ruolo, Username, Password, UserType) VALUES (NULL, ?, ?, ?, ?, ?, ?)';
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        $status = $db->query($sql, "sssssi", $name, $surname, $role, $username, $password, $admin);
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

    public static function updateEmployee(Database $db, $id, $name, $surname, $role, $username, $password, $admin){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($password)){
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, UserType = ? WHERE (id = ?)';
        } else {
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, Password = ?, UserType = ? WHERE (id = ?)';
        }
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if (empty($password)){
            $status = $db->query($sql, "ssssii", $name, $surname, $role, $username, $admin, $id);
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $status = $db->query($sql, "sssssii", $name, $surname, $role, $username, $password, $admin, $id);
        }
        if ($status) {
            //Success
            return true;
        }
        return false;
    }

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