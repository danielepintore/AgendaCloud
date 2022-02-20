<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId'])){
    // create a service object
    $service = new Service($_GET['serviceId']);
    $employees = $service->get_employees();
    // se non ci sono stati errori fornisci la risposta
    if (!$employees["error"]) {
        if (count($employees["response"]) == 0){
            print(json_encode(array()));
        } else {
            print(json_encode($employees["response"]));
        }
    } else {
        // TODO: send log
        // there is an error
        print(json_encode(array("error" => true)));
    }
} else {
    print(json_encode(array("error" => true)));
}
