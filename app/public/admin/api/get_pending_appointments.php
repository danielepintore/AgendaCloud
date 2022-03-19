<?php
use Admin\User;
require_once(realpath(dirname(__FILE__, 4)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User($_SESSION['logged'], $_SESSION['username'], $_SESSION['password'], $_SESSION['isAdmin']);
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
if (isset($_GET['date'])) {
    // create a service object
    try {
        $appointments = \Admin\Appointment::getAppointmentRequest($_SESSION['isAdmin'], $_GET['date']);
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