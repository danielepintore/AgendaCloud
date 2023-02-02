<?php

/**
 * This class is used to create the service object and handles all types of service data
 * Includes also some methods to fetch data from the database, specifically it allows to get all active services
 * and get the list of employees associated to the service.
 * This class is mainly used in the client interface but is also used in administrators pages
 */

class Service {
    private int $serviceId;
    private string $name;
    private int $duration;
    private int $waitTime;
    private int $cost;
    private string $description;
    private string $imageUrl;
    private int $bookableUntil;
    private bool $isActive;
    private bool $needTimeSupervision;
    private Database $db;
    private bool $initialized;

    /**
     * This is the main constructor and allows to have multiple constructors in the class, it chooses based on the
     * number of argument the correct constructor
     */
    public function __construct() {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        if (method_exists($this, $function = '__construct' . $numberOfArguments)) {
            call_user_func_array(array($this, $function), $arguments);
        }
    }

    /**
     * @param Database $db
     * @param $serviceId
     * @return void
     * This constructor gets all information of the service from the database using its serviceId
     * @throws DatabaseException|ServiceException
     */
    public function __construct2(Database $db, $serviceId): void {
        $this->db = $db;
        $this->serviceId = $serviceId;
        $this->initialized = true;
        $this->setServiceInfo();
    }

    /**
     * @param $db
     * @param $serviceId
     * @param $name
     * @param $duration
     * @param $waitTime
     * @param $cost
     * @param $description
     * @param $bookableUntil
     * @param $isActive
     * @return void
     * This constructor initializes the service object using the data provided by the programmer
     */
    public function __construct10($db, $serviceId, $name, $duration, $waitTime, $cost, $description, $bookableUntil,
                                 $isActive, $needTimeSupervision): void {
        $this->db = $db;
        $this->serviceId = $serviceId;
        $this->name = $name;
        $this->duration = $duration;
        $this->waitTime = $waitTime;
        $this->cost = $cost;
        $this->description = $description;
        $this->bookableUntil = $bookableUntil;
        $this->isActive = $isActive;
        $this->needTimeSupervision = $needTimeSupervision;
        $this->initialized = true;
    }

    /**
     * @return array
     * If the service is initialized correctly returns a list of ACTIVE employees associated to the service
     * @throws DatabaseException
     * @throws ServiceException
     */
    function get_employees(): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (!$this->initialized) {
            throw ServiceException::failedToGetServiceData();
        }
        $sql = 'SELECT Dipendente.id AS id, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS Nominativo FROM Dipendente, Offre WHERE (Dipendente.id = Offre.Dipendente_id AND Offre.Servizio_id = ? AND Dipendente.isActive = TRUE)';
        $status = $this->db->query($sql, "i", $this->serviceId);
        if (!$status) {
            return [];
        }
        $result = $this->db->getResult();
        $response = [];
        foreach ($result as $r) {
            $response[] = $r;
        }
        return $response;
    }

    /**
     * @return void
     * @throws DatabaseException|ServiceException
     * If we initialize the service by its serviceId this function will get all the service data from the database
     * WARNING the service MUST be ACTIVE
     * Its sets initialized to true if everything is ok
     */
    private function setServiceInfo(): void {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT * FROM Servizio WHERE(id = ? AND IsActive = TRUE)";
        $status = $this->db->query($sql, "i", $this->serviceId);
        if (!$status) {
            throw DatabaseException::confirmStatusIsFalse();
        }
        // Query succeeded
        $result = $this->db->getResult();
        if ($this->db->getAffectedRows() != 1){
            throw ServiceException::failedToGetServiceData();
        }
        $service = $result[0];
        $this->name = $service['Nome'];
        $this->duration = $service['Durata'];
        $this->cost = $service['Costo'];
        $this->waitTime = $service['TempoPausa'];
        $this->description = $service['Descrizione'];
        $this->imageUrl = $service['ImmagineUrl'];
        $this->bookableUntil = $service['BookableUntil'];
        $this->isActive = $service['IsActive'];
        $this->needTimeSupervision = 1; //TODO fetch data from the DB
    }

    /**
     * Returns an array containing basic information of the initialized service
     * @throws ServiceException
     * @return array{
     *          id: int,
     *          Nome: string,
     *          Durata: int,
     *          Costo: int
     *     }
     */
    public function getServiceInfo(): array {
        if ($this->initialized) {
            return ["id" => $this->serviceId, "Nome" => $this->name, "Durata" => $this->duration,
                "Costo" => $this->cost, "needTimeSupervision" => $this->needTimeSupervision];
        } else {
            throw ServiceException::failedToGetServiceData();
        }

    }

    /**
     * @throws DatabaseException
     * @return array{
     *          id: int,
     *          Nome: string,
     *          Durata: int,
     *          Costo: int
     *     }
     * Gets the list of active services where there is at least one active employee
     */
    public static function getActiveServices(Database $db): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.Costo FROM Servizio, Offre, Dipendente WHERE(Servizio.IsActive = TRUE AND Dipendente.IsActive = TRUE AND Servizio.id = Offre.Servizio_id AND Offre.Dipendente_id = Dipendente.id) GROUP BY Servizio.id";
        $status = $db->query($sql);
        if (!$status) {
            return [];
        }
        $result = $db->getResult();
        $response = [];
        foreach ($result as $r) {
            $response[] = ['id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'], 'Costo' => $r['Costo']];
        }
        return $response;
    }

    /**
     * @return int
     * @throws ServiceException
     */
    public function getServiceId(): int {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->serviceId;
    }

    /**
     * @return string
     * @throws ServiceException
     */
    public function getName(): string {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->name;
    }

    /**
     * @return int
     * @throws ServiceException
     */
    public function getDuration(): int {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->duration;
    }

    /**
     * @return int
     * @throws ServiceException
     */
    public function getWaitTime(): int {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->waitTime;
    }

    /**
     * @return int
     * @throws ServiceException
     */
    public function getCost(): int {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->cost;
    }

    /**
     * @return string
     * @throws ServiceException
     */
    public function getDescription(): string {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->description;
    }

    /**
     * @return string
     * @throws ServiceException
     */
    public function getImageUrl(): string {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->imageUrl;
    }

    /**
     * @return int
     * @throws ServiceException
     */
    public function getBookableUntil(): int {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->bookableUntil;
    }

    /**
     * @return bool
     * @throws ServiceException
     */
    public function getIsActive(): bool {
        if (!$this->initialized){
            throw ServiceException::failedToGetServiceData();
        }
        return $this->isActive;
    }

    /**
     * @param int $serviceId
     */
    public function setServiceId(int $serviceId): void {
        $this->serviceId = $serviceId;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @param int $duration
     */
    public function setDuration(int $duration): void {
        $this->duration = $duration;
    }

    /**
     * @param int $waitTime
     */
    public function setWaitTime(int $waitTime): void {
        $this->waitTime = $waitTime;
    }

    /**
     * @param int $cost
     */
    public function setCost(int $cost): void {
        $this->cost = $cost;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void {
        $this->description = $description;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl): void {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @param int $bookableUntil
     */
    public function setBookableUntil(int $bookableUntil): void {
        $this->bookableUntil = $bookableUntil;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void {
        $this->isActive = $isActive;
    }

}