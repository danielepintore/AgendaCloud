<?php
include("db.php");
header('Content-Type: application/json; charset=utf-8');
try {
    $db = getDB();
    if (isset($_GET['service'])){
        $sql = "SELECT * FROM Servizio WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt ->bind_param('s', $_GET['service']);
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            print(json_encode($result));
        } else {
            return json_encode(array("error" => true, "info" => "Contattare l'assistenza"));
        }
    } else {
        $sql = "SELECT * FROM Servizio";
        $stmt = $db->prepare($sql);
        if ($stmt->execute()) {
            //Success
            $result = $stmt->get_result();
            $response = array();
            foreach ($result as $r){
                $response[] = $r;
            }
            print(json_encode($response));
        } else {
            return json_encode(array("error" => true, "info" => "Contattare l'assistenza"));
        }
    }
} catch (ErrorException $e) {
    return json_encode(array("error" => true, "info" => $e->getMessage()));
}