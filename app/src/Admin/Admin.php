<?php

class Admin {
    private $isLogged;
    private $username;
    private $email;

    /**
     * @param $isLogged
     * @param $username
     * @param $email
     */
    public function __construct($isLogged, $username, $email) {
        $this->isLogged = $isLogged;
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isLogged() {
        return $this->isLogged;
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
