<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
$dipendenti = get_dipendenti();
// se non ci sono stati errori fornisci la risposta
if (!$dipendenti["error"]) {
    if (count($dipendenti["response"]) == 0){
        print(json_encode(array()));
    } else {
        print(json_encode($dipendenti["response"]));
    }
} else {
    // TODO: send log
    // there is an error
    print(json_encode(array("error" => true)));
}
