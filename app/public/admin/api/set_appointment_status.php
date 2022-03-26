<?php
use Admin\User;
require_once(realpath(dirname(__FILE__, 4)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User();
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
if (isset($_GET['appointmentId']) && isset($_GET['action'])) {
    // create a service object
    try {
        if ($_GET['action'] == "confirm"){
            $appointments = \Admin\Appointment::acceptAppointment($user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            //TODO: send email
        } else {
            $appointments = \Admin\Appointment::rejectAppointment($user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            //TODO: send email
        }
        // se non ci sono stati errori fornisci la risposta
        if ($appointments == true) {
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