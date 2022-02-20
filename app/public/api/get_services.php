<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId'])){
    $serviceObject = new Service($_GET['serviceId']);
    $services = $serviceObject->getServiceInfo();
} else {
    $services = Services::getAllServices();
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
