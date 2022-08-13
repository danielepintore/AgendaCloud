<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId']) && isset($_GET['employeeId']) &&
    is_numeric($_GET['employeeId']) && isset($_GET['date'])) {
    try {
        $db = new Database();
        $slots = Slot::getSlots($db, $_GET['serviceId'], $_GET['employeeId'], $_GET['date']);
        // se non ci sono stati errori fornisci la risposta
        if (count($slots) == 0) {
            print(json_encode(array()));
        } else {
            print(json_encode($slots));
        }
        die(0);
    } catch (DatabaseException|SlotException|EmployeeException|ServiceException|Exception $e) {
        if (DEBUG) {
            Debug::printException($e);
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
} else {
    if (DEBUG) {
        print("Something isn't set");
    } else {
        print(json_encode(array("error" => true)));
        die(0);
    }
}
