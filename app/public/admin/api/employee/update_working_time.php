<?php

use Admin\User;

require_once(realpath(dirname(__FILE__, 5)) . '/src/Api/loader.php');
session_start();
if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['logged']) && $_SESSION['logged'] && $_SESSION['isAdmin']) {
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
    if (isset($_POST['data']) && JSONUtility::validateJSON($_POST['data'])) {
        // create a service object
        try {
            $data = json_decode($_POST['data']);
            if ($data->timeType === "custom") {
                if (!(!empty($data->timeType) && is_string($data->timeType) && isset($data->startTime) && is_string($data->startTime) &&
                    isset($data->endTime) && is_string($data->endTime) && isset($data->startBreak) && is_string($data->startBreak) &&
                    isset($data->endBreak) && is_string($data->endBreak) && !empty($data->startDay) && is_string($data->startDay) &&
                    !empty($data->endDay) && is_string($data->endDay) && !empty($data->userId) && is_string($data->userId) && is_bool($data->freeDay))) {
                    if (DEBUG) {
                        print("Something isn't setted up");
                        die(0);
                    } else {
                        print(json_encode(array("error" => true)));
                        die(0);
                    }
                }
            } else {
                if (!(!empty($data->timeType) && is_string($data->timeType) && isset($data->startTime) && is_string($data->startTime) &&
                    isset($data->endTime) && is_string($data->endTime) && isset($data->startBreak) && is_string($data->startBreak) &&
                    isset($data->endBreak) && is_string($data->endBreak) && !empty($data->userId) && is_string($data->userId) && is_bool($data->freeDay))) {
                    if (DEBUG) {
                        print("Something isn't setted up");
                        die(0);
                    } else {
                        print(json_encode(array("error" => true)));
                        die(0);
                    }
                }
            }
            if (isset($_POST["method"]) && !is_null($_POST["method"]) && is_string($_POST["method"]) && $_POST["method"] == "OVERRIDE") {
                $update = \Admin\Employee::overrideCustomWorkTimes($db, $data);
            } else {
                $update = \Admin\Employee::addWorkingTimes($db, $data);
            }
            // se non ci sono stati errori fornisci la risposta
            if (!empty($update["warning"]) && $update["warning"] === "conflict") {
                print(json_encode(array("warning" => "conflict")));
            } elseif ($update) {
                print(json_encode(array("error" => false)));
            } else {
                print(json_encode(array("error" => true)));
            }
        } catch (DatabaseException|Exception $e) {
            if (DEBUG) {
                Debug::printException($e);
            } else {
                print(json_encode(array("error" => true)));
            }
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