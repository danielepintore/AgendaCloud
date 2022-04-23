<?php

namespace Admin;
use Database;

class User {
    private $id;
    private $isLogged;
    private $username;
    private $isAdmin;
    private $db;

    /**
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->id = $_SESSION["userId"];
        $this->isLogged = $_SESSION["logged"];
        $this->username = $_SESSION["username"];
        $this->db = $db;
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

    /**
     * @throws \DatabaseException
     */
    public function exist() {
        $sql = 'SELECT Dipendente.id FROM Dipendente WHERE Dipendente.id = ?';
        $status = $this->db->query($sql, "i", $this->id);
        if ($status) {
            //Success
            $this->db->getResult();
            $numRows = $this->db->getAffectedRows();
            if ($numRows == 1){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * @throws \DatabaseException
     */
    public function isActive() {
        $sql = 'SELECT Dipendente.isActive FROM Dipendente WHERE Dipendente.id = ?';
        $status = $this->db->query($sql, "i", $this->id);
        if ($status) {
            //Success
            $result = $this->db->getResult()[0];
            if ($result["isActive"] == 1){
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

}
