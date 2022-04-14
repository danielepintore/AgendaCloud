<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $user = new User();
    // check if user still exist in the database
    if (!$user->exist()){
        if (DEBUG){
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
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
if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id'])) {
    // create a service object
    try {
        $employee = \Admin\Employee::deleteEmployee($_GET['id'], $user->getId());
        // se non ci sono stati errori fornisci la risposta
        if ($employee == true) {
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
    if (DEBUG) {
        print("Something isn't setted up");
        die(0);
    } else {
        print(json_encode(array("error" => true)));
        die(0);
    }
}