<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 4)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()) {
        print(json_encode(array("error" => true)));
        die(0);
    }
    if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId']) && isset($_GET['date'])) {
        try {
            if (isset($_GET["employeeId"])) {
                $slots = Slot::getSlots($db, $_GET['serviceId'], $_GET["employeeId"], $_GET['date']);
            } else {
                $slots = Slot::getSlots($db, $_GET['serviceId'], $user->getId(), $_GET['date']);
            }
            // se non ci sono stati errori fornisci la risposta
            if (count($slots) == 0) {
                print(json_encode(array()));
            } else {
                print(json_encode($slots));
            }
        } catch (DatabaseException|SlotException|Exception $e) {
            print(json_encode(array("error" => true)));
            die(0);
        }
    } else {
        print(json_encode(array("error" => true)));
        die(0);
    }

} else {
    // user isn't logged
    // redirect to login page
    print(json_encode(array("error" => true)));
    die(0);
}