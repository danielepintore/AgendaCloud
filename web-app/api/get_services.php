<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
$services = get_services();
// se non ci sono stati errori fornisci la risposta
if (!$services["error"]) {
    if (count($services["response"]) == 0){
        print(json_encode(array()));
    } else {
        print(json_encode($services["response"]));
    }
} else {
    // TODO: send log
    // there is an error
    print(json_encode(array("error" => true)));
}
