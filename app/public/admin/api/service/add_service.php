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
    if (isset($_POST['serviceName']) && strlen($_POST['serviceName']) >= 3 && !empty($_POST['serviceName']) &&
        isset($_POST['serviceDuration']) && is_numeric($_POST['serviceDuration']) && !empty($_POST['serviceDuration']) &&
        isset($_POST['serviceStartTime']) && !empty($_POST['serviceStartTime']) && isset($_POST['serviceEndTime']) &&
        !empty($_POST['serviceEndTime']) && isset($_POST['serviceCost']) && is_numeric($_POST['serviceCost']) &&
        $_POST['serviceCost'] > 0 && isset($_POST['serviceWaitTime']) && is_numeric($_POST['serviceWaitTime']) &&
        $_POST['serviceWaitTime'] >= 0 && isset($_POST['bookableUntil']) && is_numeric($_POST['bookableUntil']) &&
        $_POST['bookableUntil'] >= 0 && isset($_POST['serviceActive'])) {
        // create a service object
        try {
            if (isset($_POST['serviceDescription'])) {
                $service = \Admin\Services::addServices($db, $_POST['serviceName'], $_POST['serviceDuration'], $_POST['serviceStartTime'],
                    $_POST['serviceEndTime'], $_POST['serviceCost'], $_POST['serviceWaitTime'], $_POST['bookableUntil'],
                    filter_var($_POST['serviceActive'], FILTER_VALIDATE_BOOLEAN), $_POST['serviceDescription']);
            } else {
                $service = \Admin\Services::addServices($db, $_POST['serviceName'], $_POST['serviceDuration'], $_POST['serviceStartTime'],
                    $_POST['serviceEndTime'], $_POST['serviceCost'], $_POST['serviceWaitTime'], $_POST['bookableUntil'], $_POST['serviceActive']);
            }
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