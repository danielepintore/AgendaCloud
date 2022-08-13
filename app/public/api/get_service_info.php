<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId'])) {
    try {
        $db = new Database();
        $serviceObject = new Service($db, $_GET['serviceId']);
        $services = $serviceObject->getServiceInfo();
        // se non ci sono stati errori fornisci la risposta
        if (count($services) == 0) {
            print(json_encode(array()));
        } else {
            print(json_encode($services));
        }
        die(0);
    } catch (DatabaseException|ServiceException|Exception $e) {
        if (DEBUG) {
            print($e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode());
        } else {
            print(json_encode(array("error" => true)));
        }
        die(0);
    }
} else {
    if (DEBUG) {
        print("Something isn't setted up");
    } else {
        print(json_encode(array("error" => true)));
    }
    die(0);
}
