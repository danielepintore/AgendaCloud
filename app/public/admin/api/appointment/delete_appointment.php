<?php
use Admin\User;
require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $database = new Database();
    $db = $database->db;
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
    if (isset($_GET['id']) && is_numeric($_GET['id']) && !empty($_GET['id'])) {
        // create a service object
        try {
            $appointment = \Admin\Appointment::deleteAppointment($db, $user->IsAdmin(), $_GET['id'], $user->getId());
            // se non ci sono stati errori fornisci la risposta
            if ($appointment) {
                print(json_encode(array("error" => false)));
            } else {
                print(json_encode(array("error" => true)));
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