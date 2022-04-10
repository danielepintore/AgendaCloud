<?php
use Admin\User;
require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $user = new User();
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
if (isset($_POST['serviceName']) && strlen($_POST['serviceName']) >= 3 && isset($_POST['serviceDuration']) && is_numeric($_POST['serviceDuration']) && isset($_POST['serviceStartTime']) &&
    isset($_POST['serviceEndTime']) && isset($_POST['serviceCost']) && is_numeric($_POST['serviceCost']) && isset($_POST['serviceWaitTime']) && is_numeric($_POST['serviceWaitTime']) &&
    isset($_POST['bookableUntil']) && is_numeric($_POST['bookableUntil']) && isset($_POST['serviceActive'])) {
    // create a service object
    try {
        if (isset($_POST['serviceDescription'])){
            $service = \Admin\Services::addServices($_POST['serviceName'], $_POST['serviceDuration'], $_POST['serviceStartTime'],
                $_POST['serviceEndTime'], $_POST['serviceCost'], $_POST['serviceWaitTime'], $_POST['bookableUntil'],
                $_POST['serviceActive'], $_POST['serviceDescription']);
        } else {
            $service = \Admin\Services::addServices($_POST['serviceName'], $_POST['serviceDuration'], $_POST['serviceStartTime'],
                $_POST['serviceEndTime'], $_POST['serviceCost'], $_POST['serviceWaitTime'], $_POST['bookableUntil'], $_POST['serviceActive']);
        }
        // se non ci sono stati errori fornisci la risposta
        if ($service == true) {
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
    print(json_encode(array("error" => true)));
    die(0);
}