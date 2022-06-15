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
    private $paymentType;
    private $db;
    private $isUserAuthenticated;

    /**
     * @param Database $db
     * @param $serviceId
     * @param $employeeId
     * @param $date
     * @param $my_slot
     * @param Client $client
     * @param $sessionId
     * @param $paymentType
     * @param $paymentStatus
     */
    public function __construct(Database $db, $serviceId, $employeeId, $date, $my_slot, Client $client, $sessionId, $paymentType, $paymentStatus, $isUserAuthenticated = false) {
        $this->db = $db;
        $this->serviceId = $serviceId;
        $this->employeeId = $employeeId;
        $this->date = $date;
        $this->my_slot = $my_slot;
        $this->client = $client;
        $this->sessionId = $sessionId;
        $this->paymentStatus = $paymentStatus;
        $this->paymentType = $paymentType;
        $this->isUserAuthenticated = $isUserAuthenticated;
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

    /**
     * @return mixed
     */
    public function getPaymentType() {
        return $this->paymentType;
    }

    /**
     * @return bool
     * @throws DatabaseException
     * @throws SlotException
     */
    public function book(): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        // get all the data from the cliente object
        $nomeCliente = $this->client->getName();
        $cognomeCliente = $this->client->getSurname();
        $emailCliente = $this->client->getEmail();
        $cellulareCliente = $this->client->getPhone();
        // add the new client to the database
        $sql = "INSERT INTO `Cliente` (`id`, `Nome`, `Cognome`, `CodiceFiscale`, `DataNascita`, `Email`, `Cellulare`) VALUES (NULL, ?, ?, NULL, NULL, ?, ?)";
        $status = $this->db->query($sql, "ssss", $nomeCliente, $cognomeCliente, $emailCliente, $cellulareCliente);
        if ($status) {
            //Query result is success
            $client_id = $this->db->getInsertId();
            // check if the current request is generated by the api
            try {
                $slots = Slot::getSlots($this->db, $this->serviceId, $this->employeeId, $this->date, $this->isUserAuthenticated);
            } catch (Exception $e){
                throw SlotException::unableToGetSlots();
            }
            $selected_slot = explode('-', $this->my_slot);
            $my_slot = array("start_time" => $selected_slot[0], "end_time" => $selected_slot[1]);
            $isAvailable = false;
            foreach ($slots as $s) {
                if ($s["start_time"] == $my_slot["start_time"] && $s["end_time"] == $my_slot["end_time"]) {
                    $isAvailable = true;
                    break;
                }
            }
            if ($isAvailable) {
                // slot presente tra quelli generati dall'api procedere con la prenotazione
                $sql = "INSERT INTO Appuntamento (id, Cliente_id, Servizio_id, Dipendente_id, Data, OraInizio, OraFine, Stato, SessionId, AddedAt, MetodoPagamento_id) VALUES (NULL, ?, ?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(), ?)";
                $status = $this->db->query($sql, "iiisssisi", $client_id, $this->serviceId, $this->employeeId, $this->date,
                    $selected_slot[0], $selected_slot[1], $this->paymentStatus, $this->sessionId, $this->paymentType);
                if ($status) {
                    //Success
                    return true;
                }
            } else {
                // slot non esiste non inserire
                throw SlotException::inesistentSlot();
            }
        }
        return false;
    }

    /**
     * @param Database $db
     * @param $session_id
     * @throws DatabaseException
     * @return bool
     * Given a session id, changes the status of the appointment to APPOINTMENT_CONFIRMED
     */
    public static function markAsPaid(Database $db, $session_id): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = "UPDATE Appuntamento SET Stato = ? WHERE (SessionId = ? AND Stato = ?)";
        $status = $db->query($sql, "isi", APPOINTMENT_CONFIRMED, $session_id, PAYMENT_PENDING);
        if ($status) {
            return true;
        } else {
            // errore nell'aggiornamento dello stato del pagamento da parte del db
            throw DatabaseException::updateOrderStatus();
        }
    }
}