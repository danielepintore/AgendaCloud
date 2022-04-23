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
    private $isActive;
    private $db;

    /**
     * @param $serviceId
     * @param $name
     * @param $duration
     * @param $startTime
     * @param $endTime
     * @param $waitTime
     * @param $cost
     * @param $description
     * @param $bookableUntil
     * @param $isActive
     * @param $db
     */

    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct'.$numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    /**
     * @param Database $db
     * @param $serviceId
     * @throws DatabaseException
     */
    public function __construct2(Database $db, $serviceId) {
        $this->db = $db;
        $this->serviceId = $serviceId;
        $this->success = true;
        $this->setServiceInfo();
    }

    public function __construct11($db, $serviceId, $name, $duration, $startTime, $endTime, $waitTime, $cost, $description, $bookableUntil, $isActive) {
        $this->serviceId = $serviceId;
        $this->name = $name;
        $this->duration = $duration;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->waitTime = $waitTime;
        $this->cost = $cost;
        $this->description = $description;
        $this->bookableUntil = $bookableUntil;
        $this->isActive = $isActive;
        $this->db = $db;
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

    /**
     * @return mixed
     */
    public function getBookableUntil() {
        return $this->bookableUntil;
    }

    /**
     * @return mixed
     */
    public function getIsActive() {
        return $this->isActive;
    }



    /**
     * @throws ServiceException
     * @throws DatabaseException
     */
    function get_employees(): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($this->success) {
            $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ? AND Dipendente.isActive = TRUE)';
            $status = $this->db->query($sql, "i", $this->serviceId);
            if ($status) {
                //Success
                $result = $this->db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = $r;
                }
                return $response;
            }
        } else {
            throw ServiceException::failedToGetServiceData();
        }
        return [];
    }

    /**
     * @return void
     * @throws DatabaseException
     */
    private function setServiceInfo(): void {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT * FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
        $status = $this->db->query($sql, "i", $this->serviceId);
        if ($status) {
            //Success
            $result = $this->db->getResult();
            $response = $result[0];
            $this->name = $response['Nome'];
            $this->duration = $response['Durata'];
            $this->startTime = $response['OraInizio'];
            $this->endTime = $response['OraFine'];
            $this->cost = $response['Costo'];
            $this->waitTime = $response['TempoPausa'];
            $this->description = $response['Descrizione'];
            $this->imageUrl = $response['ImmagineUrl'];
            $this->bookableUntil = $response['BookableUntil'];
            $this->isActive = $response['IsActive'];
        } else {
            // query failed
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

    /**
     * @throws DatabaseException
     * Gets the active services where there is at least one active employee
     */
    public static function getActiveServices(Database $db): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.OraInizio, Servizio.OraFine, Servizio.Costo FROM Servizio, Offre, Dipendente WHERE(Servizio.IsActive = TRUE AND Dipendente.IsActive = TRUE AND Servizio.id = Offre.Servizio_id AND Offre.Dipendente_id = Dipendente.id) GROUP BY Servizio.id";
        $status = $db->query($sql);
        if ($status) {
            //Success
            $result = $db->getResult();
            $response = [];
            foreach ($result as $r) {
                $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                    'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
            }
            return $response;
        }
        return [];
    }
}