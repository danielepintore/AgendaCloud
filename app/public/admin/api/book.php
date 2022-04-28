<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 4)) . '/vendor/autoload.php';
require_once(realpath(dirname(__FILE__, 4)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE &&  isset($_SESSION['logged']) && $_SESSION['logged']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()){
        print(json_encode(array("error" => true)));
        die(0);
    }
    if ($user->IsAdmin() && $user->exist() && isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date']) &&
        isset($_POST['employeeId']) && is_numeric($_POST['employeeId']) && isset($_POST['slot']) &&
        isset($_POST['clientNome']) && isset($_POST['clientCognome'])) {
        //admins bookings
        // the first thing to do is to check if the date is valid
        try {
            DateCheck::isValidDate($_POST['date'], $user->isActive());
        } catch (DataException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
        if (isset($_POST['clientEmail']) &&
            isset($_POST['clientPhone'])){
            $client = new Client($_POST['clientNome'], $_POST['clientCognome'], $_POST['clientEmail'], $_POST['clientPhone']);
        } else {
            $client = new Client($_POST['clientNome'], $_POST['clientCognome'], "", "");
        }
        try {
            $service = new Service($db, $_POST['serviceId']);
        } catch (DatabaseException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());;
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
        // valid payment method, cash selected
        // now we need to make the appointment as booked
        try {
            $appointment = new Appointment($db, $_POST['serviceId'], $_POST['employeeId'], $_POST['date'], $_POST['slot'], $client, "", CASH, WAITING_APPROVAL, $user->isActive());
            // make the reservation
            $bookResponse = $appointment->book();
            // the slot is reserved
            print(json_encode(array("error" => false)));
        } catch (DatabaseException|SlotException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
    } else if (!$user->IsAdmin() && $user->exist() && isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date'])
        && isset($_POST['slot']) && isset($_POST['clientNome']) && isset($_POST['clientCognome'])) {
        // no admin bookings
        // the first thing to do is to check if the date is valid
        try {
            DateCheck::isValidDate($_POST['date'], $user->isActive());
        } catch (DataException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
        if (isset($_POST['clientEmail']) && isset($_POST['clientPhone'])){
            $client = new Client($_POST['clientNome'], $_POST['clientCognome'], $_POST['clientEmail'], $_POST['clientPhone']);
        } else {
            $client = new Client($_POST['clientNome'], $_POST['clientCognome'], "", "");
        }
        try {
            $service = new Service($db, $_POST['serviceId']);
        } catch (DatabaseException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());;
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
        // valid payment method, cash selected
        // now we need to make the appointment as booked
        try {
            $appointment = new Appointment($db, $_POST['serviceId'], $user->getId(), $_POST['date'], $_POST['slot'], $client, "", CASH, APPOINTMENT_CONFIRMED, $user->isActive());
            // make the reservation
            $bookResponse = $appointment->book();
            // the slot is reserved
            // TODO send email to the merchant
            print(json_encode(array("error" => false)));
        } catch (DatabaseException|SlotException|Exception $e) {
            if (DEBUG) {
                print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
            } else {
                print(json_encode(array("error" => true)));
            }
            die(0);
        }
    } else {
        if (DEBUG) {
            if ($user->exist()){
                print("something isn't set");
            } else {
                print("The user no longer exist");
            }
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
} else {
    // user isn't logged
    // redirect to login page
    print(json_encode(array("error" => true)));
    die(0);
}