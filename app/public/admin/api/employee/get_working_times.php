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
    try {
        if (isset($_GET['employeeId']) && is_numeric($_GET['employeeId']) && !empty($_GET['employeeId'])) {
            $workingTimes = \Admin\Employee::getWorkingTimes($db, $_GET['employeeId']);
            // se non ci sono stati errori fornisci la risposta
            if (count($workingTimes) == 0) {
                print(json_encode(array()));
            } else {
                print(json_encode($workingTimes));
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
    } catch (DatabaseException|Exception $e) {
        if (DEBUG) {
            Debug::printException($e);
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