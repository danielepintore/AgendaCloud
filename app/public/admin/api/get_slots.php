<?php
require_once(realpath(dirname(__FILE__, 4)) . '/src/Api/loader.php');
session_start();
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId']) && isset($_GET['date'])) {
    try {
        if (isset($_GET["employeeId"])){
            $slots = Slot::getSlots($_GET['serviceId'], $_GET["employeeId"], $_GET['date']);
        } else {
            $user = new \Admin\User();
            $slots = Slot::getSlots($_GET['serviceId'], $user->getId() , $_GET['date']);
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
