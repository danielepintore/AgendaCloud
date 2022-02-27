<?php
namespace Admin;
class User {
    private $isLogged;
    private $username;
    private $email;
    private $isAdmin;

    /**
     * @param $isLogged
     * @param $username
     * @param $email
     */
    public function __construct($isLogged, $username, $email, $isAdmin) {
        $this->isLogged = $isLogged;
        $this->username = $username;
        $this->email = $email;
        $this->isAdmin = $isAdmin;
    }

    /**
     * @return bool
     */
    public function isLogged() {
        return $this->isLogged;
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return $this->isAdmin;
    }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

}
