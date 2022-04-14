<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User();
    // check if user still exist in the database
    if (!$user->exist()) {
        if (DEBUG) {
            print("The user no longer exist");
        } else {
            print(json_encode(array("error" => true)));
        }
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
if (isset($_GET['appointmentId']) && is_numeric($_GET['appointmentId']) && !empty($_GET['appointmentId']) &&
    isset($_GET['action']) && !empty($_GET['action'])) {
    // create a service object
    try {
        if ($_GET['action'] == "confirm") {
            $appointments = \Admin\Appointment::acceptAppointment($user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            //TODO: add a check if somebody have already accepted the appointment
        } else {
            $appointments = \Admin\Appointment::rejectAppointment($user->IsAdmin(), $_GET['appointmentId'], $user->getId());
            //TODO: add a check if somebody have already reject the appointment
            //TODO: send email
        }
        // se non ci sono stati errori fornisci la risposta
        if ($appointments == true) {
            print(json_encode(array("error" => false)));
            try {
                $appointment = \Admin\Appointment::fetchAppointmentInfo($_GET['appointmentId']);
                if ($_GET['action'] == "confirm") {
                    $body = MailClient::getConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                    $altBody = MailClient::getAltConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                } else {
                    $body = MailClient::getRejectOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                    $altBody = MailClient::getAltRejectOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
                }
                if (!empty($appointment->email)){
                    MailClient::addMailToQueue("La tua prenotazione", $body, $altBody, $appointment->email, $appointment->name);
                }
            } catch (Exception $e) {
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