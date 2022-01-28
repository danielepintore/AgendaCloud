<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId']) && isset($_GET['workerId']) &&
    is_numeric($_GET['workerId']) && isset($_GET['date'])) {
    $slots = get_slots($_GET['serviceId'], $_GET['workerId'], $_GET['date']);
    // se non ci sono stati errori fornisci la risposta
    if (!$slots["error"]) {
        if (count($slots["response"]) == 0) {
            print(json_encode(array()));
        } else {
            print(json_encode($slots["response"]));
        }
    } else {
        // TODO: send log
        // there is an error
        print(json_encode(array("error" => true)));
    }
} else {
    print(json_encode(array("error" => true)));
}
