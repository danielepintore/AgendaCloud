<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE &&  isset($_SESSION['logged']) && $_SESSION['logged']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()) {
        if (DEBUG) {
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
    if (isset($_GET['appointmentId']) && is_numeric($_GET['appointmentId']) && !empty($_GET['appointmentId']) &&
        isset($_GET['action']) && !empty($_GET['action'])) {
        // create a service object
        try {
            if ($_GET['action'] == "confirm") {
                $appointments = \Admin\Appointment::acceptAppointment($db, $user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            } else {
                $appointments = \Admin\Appointment::rejectAppointment($db, $user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            }
            // se non ci sono stati errori fornisci la risposta
            if ($appointments) {
                print(json_encode(array("error" => false)));
                try {
                    $appointment = \Admin\Appointment::fetchAppointmentInfo($db, $_GET['appointmentId']);
                    if ($_GET['action'] == "confirm") {
                        $body = MailClient::getConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                        $altBody = MailClient::getAltConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                    } else {
                        $body = MailClient::getRejectOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                        $altBody = MailClient::getAltRejectOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                    }
                    if (!empty($appointment->email)){
                        MailClient::addMailToQueue($db,"La tua prenotazione", $body, $altBody, $appointment->email, $appointment->name);
                    }
                    die(0);
                } catch (Exception $e) {
                    if (DEBUG){
                        print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
                        die(0);
                    }
                    $config = Config::getConfig();
                    $body = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
                    $phpMailer = new MailClient();
                    $phpMailer->sendEmail("There are problems to add mails in the queue", $body, $body, $config->mail->supervisor);
                }
            } else {
                if (DEBUG) {
                    print("The appointment doesn't exist");
                } else {
                    print(json_encode(array("error" => true)));
                }
                die(0);
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
    if (DEBUG) {
        print("There is no current logged user");
    } else {
        print(json_encode(array("error" => true)));
    }
    die(0);
}