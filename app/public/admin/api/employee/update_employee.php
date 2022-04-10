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
if (isset($_POST['id']) && is_numeric($_POST['id']) && !empty($_POST['id']) && isset($_POST['name']) && !empty($_POST['name']) &&
    isset($_POST['surname']) && !empty($_POST['surname']) && isset($_POST['role']) && !empty($_POST['role']) &&
    isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password']) &&
    isset($_POST['admin'])) {
    // create a service object
    try {
        $service = \Admin\Employee::updateEmployee($_POST['id'], $_POST['name'], $_POST['surname'], $_POST['role'],
            $_POST['username'], $_POST['password'], $_POST['admin']);
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