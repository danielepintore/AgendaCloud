<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
class Appointment {
    private $serviceId;
    private $employeeId;
    private $date;
    private $my_slot;
    private $client;
    private $sessionId;
    private $paymentStatus;

    /**
     * @param $serviceId
     * @param $employeeId
     * @param $date
     * @param $my_slot
     * @param $client
     * @param $sessionId
     * @param $paymentStatus
     */
    public function __construct($serviceId, $employeeId, $date, $my_slot, Client $client, $sessionId, $paymentStatus) {
        $this->serviceId = $serviceId;
        $this->employeeId = $employeeId;
        $this->date = $date;
        $this->my_slot = $my_slot;
        $this->client = $client;
        $this->sessionId = $sessionId;
        $this->paymentStatus = $paymentStatus;
    }

    /**
     * @return mixed
     */
    public function getServiceId() {
        return $this->serviceId;
    }

    /**
     * @return mixed
     */
    public function getEmployeeId() {
        return $this->employeeId;
    }

    /**
     * @return mixed
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getMySlot() {
        return $this->my_slot;
    }

    /**
     * @return mixed
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function getSessionId() {
        return $this->sessionId;
    }

    /**
     * @return mixed
     */
    public function getPaymentStatus() {
        return $this->paymentStatus;
    }

    public function book() : array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            // add the new client to the database
            $sql = "INSERT INTO `Cliente` (`id`, `Nome`, `Cognome`, `CodiceFiscale`, `DataNascita`, `Email`, `Cellulare`) VALUES (NULL, ?, ?, NULL, NULL, ?, ?) ";
            $stmt = $db->prepare($sql);
            // get all the data from the cliente object
            $nomeCliente = $this->client->getName();
            $cognomeCliente = $this->client->getSurname();
            $emailCliente = $this->client->getEmail();
            $cellulareCliente = $this->client->getPhone();
            // prepare the query
            $stmt ->bind_param('ssss', $nomeCliente, $cognomeCliente, $emailCliente, $cellulareCliente);
            if ($stmt->execute()) {
                //Query result is success
                $client_id = $stmt->insert_id;
                // check if the current request is generated by the api
                $slots = Slot::getSlots($this->serviceId, $this->employeeId, $this->date);
                if (!$slots["error"] && count($slots["response"]) > 0){
                    $slots = $slots["response"];
                    $selected_slot = explode('-', $this->my_slot);
                    $my_slot = array("start_time" => $selected_slot[0], "end_time" => $selected_slot[1]);
                    $isAvailable = false;
                    foreach ($slots as $s){
                        if ($s["start_time"] == $my_slot["start_time"] && $s["end_time"] == $my_slot["end_time"]){
                            $isAvailable = true;
                            break;
                        }
                    }
                    if ($isAvailable){
                        // slot presente tra quelli generati dall'api procedere con la prenotazione
                        $sql = "INSERT INTO Appuntamento (id, Cliente_id, Servizio_id, Dipendente_id, Data, OraInizio, OraFine, Stato, SessionId) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?) ";
                        $stmt = $db->prepare($sql);
                        $stmt ->bind_param('iiisssss', $client_id, $this->serviceId, $this->employeeId, $this->date, $selected_slot[0], $selected_slot[1], $this->paymentStatus, $this->sessionId);
                        if ($stmt->execute()) {
                            //Success
                            return array("error" => false, "response" => "ok");
                        } else {
                            return array("error" => true, "info" => "Contatta l'assistenza");
                        }
                    } else {
                        // slot non esiste non inserire
                        return array("error" => true, "info" => "Slot non disponibile");
                    }
                } else {
                    //error from the method to get the slots availables
                    return array("error" => true, "info" => "Contatta l'assistenza");
                }
            } else {
                return array("error" => true, "info" => "Contatta l'assistenza");
            }
        } catch (ErrorException $e) {
            return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
        }

    }

}