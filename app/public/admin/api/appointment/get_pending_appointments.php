<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
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
    if (isset($_GET['date']) && !empty($_GET['date'])) {
        // create a service object
        try {
            $appointments = \Admin\Appointment::getAppointmentRequest($db, $user->IsAdmin(), $user->getId());
            // se non ci sono stati errori fornisci la risposta
            if (count($appointments) == 0) {
                print(json_encode(array()));
            } else {
                print(json_encode($appointments));
            }
        } catch (DatabaseException|Exception $e) {
            print(json_encode(array("error" => true)));
            die(0);
        }
    } else {
        print(json_encode(array("error" => true)));
        die(0);
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