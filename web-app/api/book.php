<?php
include("utils.php");
header('Content-Type: application/json; charset=utf-8');
if (isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date']) &&
    isset($_POST['workerId']) && is_numeric($_POST['workerId']) && isset($_POST['slot'])){
    $book = book($_POST['serviceId'], $_POST['workerId'], $_POST['date'], $_POST['slot'], $_POST['client']);
    // se non ci sono stati errori fornisci la risposta
    if (!$book["error"]) {
        print(json_encode($book["response"]));
    } else {
        // TODO: send log
        // there is an error
        print(json_encode(array("error" => true, "info" => $book["info"])));
    }
} else {
    print(json_encode(array("error" => true)));
}