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
try {
    if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id']) && isset($_GET['name'])) {
        $services = \Admin\Services::getEmployeesStatusForService($_GET['id'], $_GET['name']);
    } else {
        if (DEBUG) {
            print("Something isn't setted up");
            die(0);
        } else {
            print(json_encode(array("error" => true)));
            die(0);
        }
    }
    // se non ci sono stati errori fornisci la risposta
    if (count($services) == 0) {
        print(json_encode(array()));
    } else {
        print(json_encode($services));
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