<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
$slots = get_slots();
// se non ci sono stati errori fornisci la risposta
if (!$slots["error"]) {
    if (count($slots["response"]) == 0){
        print(json_encode(array()));
    } else {
        print(json_encode($slots["response"]));
    }
} else {
    // TODO: send log
    // there is an error
    print(json_encode(array("error" => true)));
}
