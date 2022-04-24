<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE &&  isset($_SESSION['logged']) && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()){
        if (DEBUG){
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
    if (isset($_POST['name']) && !empty($_POST['name']) && isset($_POST['surname']) && !empty($_POST['surname']) &&
        isset($_POST['role']) && !empty($_POST['role']) && isset($_POST['username']) && !empty($_POST['username']) &&
        isset($_POST['password']) && !empty($_POST['password']) && isset($_POST['admin'])) {
        // create a service object
        // check the password
        if (Password::isStrong($_POST['password'])) {
            try {

                $service = \Admin\Employee::addEmployee($db, $_POST['name'], $_POST['surname'], $_POST['role'],
                    $_POST['username'], $_POST['password'], filter_var($_POST['admin'], FILTER_VALIDATE_BOOLEAN),
                    filter_var($_POST['isActive'], FILTER_VALIDATE_BOOLEAN));
                // se non ci sono stati errori fornisci la risposta
                if ($service) {
                    print(json_encode(array("error" => false)));
                } else {
                    print(json_encode(array("error" => true)));
                }
            } catch (DatabaseException|Exception $e) {
                if (DEBUG) {
                    print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());;
                    die(0);
                } else {
                    print(json_encode(array("error" => true)));
                    die(0);
                }
            }
        } else {
            // password is not strong
            print(json_encode(array("error" => true)));
        }
    } else {
        print(json_encode(array("error" => true)));
        die(0);
    }
} else {
    // user isn't logged
    // display error
    if (DEBUG){
        print("There is no current logged user");
    } else {
        print(json_encode(array("error" => true)));
    }
    die(0);
}