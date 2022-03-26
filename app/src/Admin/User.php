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
        if ($_SESSION["isAdmin"] == 0){
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

}
