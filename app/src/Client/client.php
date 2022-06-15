<?php

/**
 * Client class, contains all the information about a client
 */
class Client {
    private string $name;
    private string $surname;
    private string $email;
    private string $phone;

    /**
     * @param $name
     * @param $surname
     * @param $email
     * @param $phone
     * @throws ClientException
     */
    public function __construct($name, $surname, $email, $phone) {
        if (is_null($name) || is_null($surname) || is_null($email) || is_null($phone)){
            throw ClientException::invalidClientData();
        }
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSurname(): string {
        return $this->surname;
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone(): string {
        return $this->phone;
    }
}