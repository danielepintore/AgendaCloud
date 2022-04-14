<?php

namespace Admin;
class User {
    private $id;
    private $isLogged;
    private $username;
    private $isAdmin;

    /**
     * @param $isLogged
     * @param $username
     * @param $email
     */
    public function __construct() {
        $this->id = $_SESSION["userId"];
        $this->isLogged = $_SESSION["logged"];
        $this->username = $_SESSION["username"];
        if ($_SESSION["isAdmin"] == 0) {
            $this->isAdmin = false;
        } else {
            $this->isAdmin = true;
        }
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function IsLogged() {
        return $this->isLogged;
    }

    /**
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function IsAdmin() {
        return $this->isAdmin;
    }

    public function exist() {
        $db = \Database::getDB();
        $sql = 'SELECT Dipendente.id FROM Dipendente WHERE Dipendente.id = ?';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $this->id)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            $numRows = $stmt->get_result()->num_rows;
            if ($numRows == 1){
                return true;
            } else {
                return false;
            }
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }

}
