<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['logged']) && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()) {
        if (DEBUG) {
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
    if (isset($_POST['id']) && is_numeric($_POST['id']) && !empty($_POST['id']) && isset($_POST['serviceName']) &&
        strlen($_POST['serviceName']) >= 3 && !empty($_POST['serviceName']) && isset($_POST['serviceDuration']) &&
        is_numeric($_POST['serviceDuration']) && !empty($_POST['serviceDuration']) &&
        isset($_POST['serviceCost']) && is_numeric($_POST['serviceCost']) && isset($_POST['serviceWaitTime']) &&
        is_numeric($_POST['serviceWaitTime']) && isset($_POST['bookableUntil']) && is_numeric($_POST['bookableUntil']) &&
        isset($_POST['serviceActive']) && isset($_POST['serviceDescription'])) {
        // create a service object
        try {
            $serviceData = new Service($db, $_POST['id'], $_POST['serviceName'], $_POST['serviceDuration'],
                $_POST['serviceWaitTime'], $_POST['serviceCost'], $_POST['serviceDescription'], $_POST['bookableUntil'],
                filter_var($_POST['serviceActive'], FILTER_VALIDATE_BOOLEAN));
            $service = \Admin\Services::updateService($db, $serviceData);
            // se non ci sono stati errori fornisci la risposta
            if ($service) {
                print(json_encode(array("error" => false)));
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        } catch (DatabaseException|Exception $e) {
            if (DEBUG) {
                Debug::printException($e);
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
    } else {
        if (DEBUG) {
            print("Something isn't setted up");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
} else {
    // user isn't logged
    // display error
    if (DEBUG) {
        print("There is no current logged user");
    } else {
        print(json_encode(array("error" => true)));
    }
    die(0);
}