<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $database = new Database();
    $db = $database->db;
    $user = new User($db);
    // check if user still exist in the database
    if (!$user->exist()) {
        if (DEBUG) {
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
    try {
        if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id'])) {
            $services = \Admin\Services::getServiceList($db, $_GET['id']);
        } else {
            $services = \Admin\Services::getServiceList($db);
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