<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
if (isset($_GET['service']) && is_numeric($_GET['service'])){
    $dipendenti = get_dipendenti($_GET['service']);
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
} else {
    print(json_encode(array("error" => true)));
}
