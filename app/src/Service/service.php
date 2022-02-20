<?php
class Service {
    private $serviceId;
    private $name;
    private $duration;
    private $startTime;
    private $endTime;
    private $waitTime;
    private $cost;
    private $description;
    private $imageUrl;
    private $success;

    /**
     * @param $serviceId
     */
    public function __construct($serviceId) {
        $this->serviceId = $serviceId;
        $this->success = true;
        $this->setServiceInfo();
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
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @return mixed
     */
    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * @return mixed
     */
    public function getWaitTime() {
        return $this->waitTime;
    }

    /**
     * @return mixed
     */
    public function getCost() {
        return $this->cost;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getImageUrl() {
        return $this->imageUrl;
    }


    function get_employees() : array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($this->success){
            try {
                $db = Database::getDB();
                $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
                $stmt = $db->prepare($sql);
                $stmt ->bind_param('i', $this->serviceId);
                if ($stmt->execute()) {
                    //Success
                    $result = $stmt->get_result();
                    $response = array();
                    foreach ($result as $r){
                        $response[] = $r;
                    }
                    return(array("error" => false, "response" => $response));
                } else {
                    return array("error" => true, "info" => "Contattare l'assistenza");
                }
            } catch (ErrorException $e) {
                return array("error" => true, "info" => $e->getMessage()); // TODO change this (remove getMessage)
            }
        } else {
            return array("error" => true, "info" => "Contattare l'assistenza");
        }
    }

    private function setServiceInfo() : void {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        try {
            $db = Database::getDB();
            $sql = "SELECT * FROM Servizio WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i', $this->serviceId);
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = $result->fetch_assoc();
                $this->name = $response['Nome'];
                $this->duration = $response['Durata'];
                $this->startTime = $response['OraInizio'];
                $this->endTime = $response['OraFine'];
                $this->cost = $response['Costo'];
                $this->waitTime = $response['TempoPausa'];
                $this->description = $response['Descrizione'];
                $this->imageUrl = $response['ImmagineUrl'];
            } else {
                $this->name = null;
                $this->duration = null;
                $this->startTime = null;
                $this->endTime = null;
                $this->cost = null;
                $this->waitTime = null;
                $this->description = null;
                $this->imageUrl = null;
                $this->success = false;
            }
        } catch (ErrorException $e) {
            $this->name = null;
            $this->duration = null;
            $this->startTime = null;
            $this->endTime = null;
            $this->cost = null;
            $this->waitTime = null;
            $this->description = null;
            $this->imageUrl = null;
            $this->success = false;
        }
    }

    public function getServiceInfo() : array {
        if ($this->success){
            return array("error" => false, "response" => array("id" => $this->serviceId ,"Nome" => $this->name, "Durata" => $this->duration,
                "OraInizio" => $this->startTime, "OraFine" => $this->endTime, "Costo" => $this->cost,
                "TempoPausa" => $this->waitTime, "Descrizione" => $this->description,
                "ImmagineUrl" => $this->imageUrl));
        } else {
            return array("true" => false, "info" => "Contatta l'assistenza"); // TODO edit this error message
        }

    }
}