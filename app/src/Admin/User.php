<?php

namespace Admin;
use Database;

class User {
    private int $id;
    private bool $isLogged;
    private string $username;
    private bool $isAdmin;
    private Database $db;

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
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function IsLogged(): bool {
        return $this->isLogged;
    }

    /**
     * @return string
     */
    public function getUsername(): string {
        return $this->username;
    }

    /**
     * @return bool
     */
    public function IsAdmin(): bool {
        return $this->isAdmin;
    }

    /**
     * @throws \DatabaseException
     * Returns true if the user exist otherwise false
     */
    public function exist(): bool {
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
     * Return true if the user is active otherwise false
     */
    public function isActive(): bool {
        //TODO migrate exist method here
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
