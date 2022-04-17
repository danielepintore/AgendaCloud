<?php
namespace Admin;
use Database;
use DatabaseException;

class Employee {
    public static function getEmployees($db, $id = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($id == null){
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType FROM Dipendente';
        } else {
            $sql = 'SELECT id, Nome, Cognome, Ruolo, Username, UserType FROM Dipendente WHERE id = ?';
        }
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($id != null){
            if (!$stmt->bind_param('i',$id)) {
                throw DatabaseException::bindingParamsFailed();
            }
        }
        if ($stmt->execute()) {
            //Success
            $employees = array();
            $result = $stmt->get_result();
            foreach ($result as $r) {
                $employees[] = array("id" => $r['id'], "name" => $r['Nome'], "surname" => $r['Cognome'],
                    "role" => $r['Ruolo'], "username" => $r['Username'], "userType" => $r['UserType']);
            }
            return $employees;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        return false;
    }

    public static function addEmployee($db, $name, $surname, $role, $username, $password, $admin){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'INSERT INTO Dipendente (id, Nome, Cognome, Ruolo, Username, Password, UserType) VALUES (NULL, ?, ?, ?, ?, ?, ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        $password = password_hash($password, PASSWORD_DEFAULT);
        if (!$stmt->bind_param('sssssi', $name, $surname, $role, $username, $password, $admin)) {
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

    public static function updateEmployee($db, $id, $name, $surname, $role, $username, $password, $admin){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($password)){
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, UserType = ? WHERE (id = ?)';
        } else {
            $sql = 'UPDATE Dipendente SET Nome = ?, Cognome = ?, Ruolo = ?, Username = ?, Password = ?, UserType = ? WHERE (id = ?)';
        }
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($admin) {
            $admin = ADMIN_USER;
        } else {
            $admin = WORKER_USER;
        }
        if (empty($password)){
            if (!$stmt->bind_param('ssssii', $name, $surname, $role, $username, $admin, $id)) {
                throw DatabaseException::bindingParamsFailed();
            }
        } else {
            $password = password_hash($password, PASSWORD_DEFAULT);
            if (!$stmt->bind_param('sssssii', $name, $surname, $role, $username, $password, $admin, $id)) {
                throw DatabaseException::bindingParamsFailed();
            }
        }
        if ($stmt->execute()) {
            //Success
            return true;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        return false;
    }

    public static function deleteEmployee($db, $employeId, $loggedId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($loggedId != $employeId){
            $sql = 'DELETE FROM Dipendente WHERE Dipendente.id = ?';
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('i', $employeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                return true;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
        throw DatabaseException::cantDeleteCurrentUser();
    }
}