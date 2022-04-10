<?php

use Admin\User;
use Admin\Employee;

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
    if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id'])) {
        $employees = Employee::getEmployees($_GET['id']);
    } else {
        $employees = Employee::getEmployees();
    }
    // se non ci sono stati errori fornisci la risposta
    if (count($employees) == 0) {
        print(json_encode(array()));
    } else {
        print(json_encode($employees));
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