<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database
    if (!$user->exist()){
        if (DEBUG){
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
    if (isset($_POST['id']) && is_numeric($_POST['id']) && !empty($_POST['id']) && isset($_POST['serviceName']) &&
        strlen($_POST['serviceName']) >= 3 && !empty($_POST['serviceName']) && isset($_POST['serviceDuration']) &&
        is_numeric($_POST['serviceDuration']) && !empty($_POST['serviceDuration']) && isset($_POST['serviceStartTime']) &&
        !empty($_POST['serviceStartTime']) && isset($_POST['serviceEndTime']) && !empty($_POST['serviceEndTime']) &&
        isset($_POST['serviceCost']) && is_numeric($_POST['serviceCost']) && isset($_POST['serviceWaitTime']) &&
        is_numeric($_POST['serviceWaitTime']) && isset($_POST['bookableUntil']) && is_numeric($_POST['bookableUntil']) &&
        isset($_POST['serviceActive']) && isset($_POST['serviceDescription'])) {
        // create a service object
        try {
            $serviceData = new Service($db, $_POST['id'], $_POST['serviceName'], $_POST['serviceDuration'], $_POST['serviceStartTime'],
                $_POST['serviceEndTime'], $_POST['serviceWaitTime'], $_POST['serviceCost'], $_POST['serviceDescription'], $_POST['bookableUntil'],
                filter_var($_POST['serviceActive'], FILTER_VALIDATE_BOOLEAN));
            $service = \Admin\Services::updateService($db, $serviceData);
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
        if (DEBUG) {
            print("Something isn't setted up");
            die(0);
        } else {
            print(json_encode(array("error" => true)));
            die(0);
        }
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