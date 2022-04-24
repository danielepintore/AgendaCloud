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
    if (isset($_GET['employeeId']) && !empty($_GET['employeeId']) && is_numeric($_GET['employeeId']) &&
        isset($_GET['holidayDate']) && !empty($_GET['holidayDate']) && isset($_GET['holidayStartTime']) &&
        !empty($_GET['holidayStartTime']) && isset($_GET['holidayEndTime']) && !empty($_GET['holidayEndTime'])) {
        // create a service object
        try {
            $status = \Admin\Employee::addHoliday($db, $_GET['employeeId'], $_GET['holidayDate'], $_GET['holidayStartTime'], $_GET['holidayEndTime']);
            // se non ci sono stati errori fornisci la risposta
            if ($status) {
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
        if (DEBUG){
            print("something isn't set");
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