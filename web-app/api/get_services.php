<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
if (isset($_GET['service']) && is_numeric($_GET['service'])){
    $services = get_services($_GET['service']);
} else {
    $services = get_services();
}
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
