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
    private $bookableUntil;
    private $db;

    /**
     * @param $serviceId
     * @throws DatabaseException
     */
    public function __construct($db, $serviceId) {
        $this->db = $db;
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


    function get_employees(): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($this->success) {
            $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ?)';
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('i', $this->serviceId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = $r;
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            throw ServiceException::failedToGetServiceData();
        }
    }

    /**
     * @return void
     * @throws DatabaseException
     */
    private function setServiceInfo(): void {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT * FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            $this->success = false;
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $this->serviceId)) {
            $this->success = false;
            throw DatabaseException::bindingParamsFailed();
        }
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
            $this->bookableUntil = $response['BookableUntil'];
        } else {
            $this->name = null;
            $this->duration = null;
            $this->startTime = null;
            $this->endTime = null;
            $this->cost = null;
            $this->waitTime = null;
            $this->description = null;
            $this->imageUrl = null;
            $this->bookableUntil = null;
            $this->success = false;
            throw DatabaseException::queryExecutionFailed();
        }
    }

    /**
     * @return array
     * @throws ServiceException
     */
    public function getServiceInfo(): array {
        if ($this->success) {
            return array("id" => $this->serviceId, "Nome" => $this->name, "Durata" => $this->duration,
                "OraInizio" => $this->startTime, "OraFine" => $this->endTime, "Costo" => $this->cost);
        } else {
            throw ServiceException::failedToGetServiceData();
        }

    }
}