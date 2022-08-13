<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId'])) {
    $db = new Database();
    // create a service object
    try {
        $service = new Service($db, $_GET['serviceId']);
        $employees = $service->get_employees();
        // se non ci sono stati errori fornisci la risposta
        if (count($employees) == 0) {
            print(json_encode(array()));
        } else {
            print(json_encode($employees));
        }
        die(0);
    } catch (DatabaseException|ServiceException|Exception $e) {
        print(json_encode(array("error" => true)));
        die(0);
    }
} else {
    if (DEBUG) {
        print("Something isn't setted");
    } else {
        print(json_encode(array("error" => true)));
    }
    die(0);
}
