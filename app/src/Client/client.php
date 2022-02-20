<?php
class Client {
    private $name;
    private $surname;
    private $email;
    private $phone;

    /**
     * @param $name
     * @param $surname
     * @param $email
     * @param $phone
     */
    public function __construct($name, $surname, $email, $phone) {
        $this->name = $name;
        $this->surname = $surname;
        $this->email = $email;
        $this->phone = $phone;
    }


    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getSurname() {
        return $this->surname;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getPhone() {
        return $this->phone;
    }

}