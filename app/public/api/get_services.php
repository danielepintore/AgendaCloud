<?php
require_once(realpath(dirname(__FILE__, 3)) . '/src/Api/loader.php');
if (isset($_GET['serviceId']) && is_numeric($_GET['serviceId'])) {
    try {
        $database = new Database();
        $db = $database->db;
        $serviceObject = new Service($db, $_GET['serviceId']);
        $services = $serviceObject->getServiceInfo();
        // se non ci sono stati errori fornisci la risposta
            if (count($services) == 0) {
                print(json_encode(array()));
            } else {
                print(json_encode($services));
            }
    } catch (DatabaseException | ServiceException | Exception $e){
        print(json_encode(array("error" => true)));
        die(0);
    }

} else {
    print(json_encode(array("error" => true)));
    die(0);
}
