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
if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id'])) {
    // create a service object
    try {
        $employee = \Admin\Employee::deleteEmployee($_GET['id']);
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