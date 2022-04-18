<?php

namespace Admin;

use Database;
use DatabaseException;

class Appointment {
    /**
     * @throws DatabaseException
     * @throws \DataException
     */
    public static function getAppointments(Database $db, $isAdmin, $date, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            if (\DateCheck::isToday($date)) {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            } else {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            }
            $status = $db->query($sql, "si", $date, APPOINTMENT_CONFIRMED);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Stato' => $r['Stato'], 'TipoPagamento' => $r['TipoPagamento'],
                        'Data' => $r['Data']);
                }
                return $response;
            }
        } else {
            if (\DateCheck::isToday($date)) {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND CURRENT_TIME() <= Appuntamento.OraFine AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            } else {
                $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Appuntamento.Data = ? AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.Stato = ?) ORDER BY Appuntamento.OraInizio';
            }
            $status = $db->query($sql, "isi", $employeeId, $date, APPOINTMENT_CONFIRMED);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Stato' => $r['Stato'], 'TipoPagamento' => $r['TipoPagamento'],
                        'Data' => $r['Data']);
                }
                return $response;
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function getAppointmentRequest(Database $db, $isAdmin, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Cliente.id = Appuntamento.Cliente_id AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.MetodoPagamento_id = ? AND Appuntamento.Stato = ? AND CONCAT(Appuntamento.Data, Appuntamento.OraInizio) >= CONCAT(CURDATE(), CURTIME())) ORDER BY Appuntamento.Data, Appuntamento.OraInizio';
            $status = $db->query($sql, "ii", CASH, WAITING_APPROVAL);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Data' => $r['Data']);
                }
                return $response;
            }
        } else {
            $sql = 'SELECT CONCAT(Cliente.Nome, " ", Cliente.Cognome) AS NominativoC, CONCAT(Dipendente.Nome, " ", Dipendente.Cognome) AS NominativoD, Servizio.Nome AS NomeServizio, Cliente.Cellulare, Appuntamento.id, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine, MetodoPagamento.Nome AS NomePagamento, Appuntamento.Stato AS Stato, MetodoPagamento.id AS TipoPagamento, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data FROM Cliente, Dipendente, Appuntamento, Servizio, MetodoPagamento WHERE (Appuntamento.Dipendente_id = ? AND Cliente.id = Appuntamento.Cliente_id AND Dipendente.id = Appuntamento.Dipendente_id AND Servizio.id = Appuntamento.Servizio_id AND MetodoPagamento.id = Appuntamento.MetodoPagamento_id AND Appuntamento.MetodoPagamento_id = ? AND Appuntamento.Stato = ? AND CONCAT(Appuntamento.Data, Appuntamento.OraInizio) >= CONCAT(CURDATE(), CURTIME())) ORDER BY Appuntamento.Data, Appuntamento.OraInizio';
            $status = $db->query($sql, "iii", $employeeId, CASH, WAITING_APPROVAL);
            if ($status) {
                //Success
                $result = $db->getResult();
                $response = [];
                foreach ($result as $r) {
                    $response[] = array('appointmentId' => $r['id'], 'NominativoCliente' => $r['NominativoC'],
                        'NominativoDipendente' => $r['NominativoD'], 'NomeServizio' => $r['NomeServizio'],
                        'NomePagamento' => $r['NomePagamento'], 'Cellulare' => $r['Cellulare'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Data' => $r['Data']);
                }
                return $response;
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function acceptAppointment(Database $db, $isAdmin, $appointmentId, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.MetodoPagamento_id = 2';
            $status = $db->query($sql, "iii", APPOINTMENT_CONFIRMED, $appointmentId, WAITING_APPROVAL);
            if ($status) {
                //Success
                if ($db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.MetodoPagamento_id = 2';
            $status = $db->query($sql, "iiii", APPOINTMENT_CONFIRMED, $appointmentId, WAITING_APPROVAL, $employeeId);
            if ($status) {
                //Success
                if ($db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function rejectAppointment(Database $db, $isAdmin, $appointmentId, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.MetodoPagamento_id = 2';
            $status = $db->query($sql, "iii", REJECTED_BY_USER, $appointmentId, WAITING_APPROVAL);
            if ($status) {
                //Success
                if ($db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Stato = ? AND Appuntamento.Dipendente_id = ? AND Appuntamento.MetodoPagamento_id = 2';
            $status = $db->query($sql, "iiii", REJECTED_BY_USER, $appointmentId, WAITING_APPROVAL, $employeeId);
            if ($status) {
                //Success
                if ($db->getAffectedRows() == 1) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function deleteAppointment(Database $db, $isAdmin, $appointmentId, $employeeId = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ?';
            $status = $db->query($sql, "ii", CANCELED, $appointmentId);
            if ($status) {
                //Success
                return true;
            }
        } else {
            $sql = 'UPDATE Appuntamento SET Stato = ? WHERE Appuntamento.id = ? AND Appuntamento.Dipendente_id = ?';
            $status = $db->query($sql, "iii", CANCELED, $appointmentId, $employeeId);
            if ($status) {
                //Success
                return true;
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     */
    public static function fetchAppointmentInfo(Database $db, $appointmentId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT Cliente.Nome, Cliente.Email, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine FROM Appuntamento, Cliente WHERE (Appuntamento.id = ? AND Appuntamento.Cliente_id = Cliente.id);';
        $status = $db->query($sql, "i", $appointmentId);
        if ($status) {
            //Success, we should find only one appointment
            $result = $db->getResult();
            if ($db->getAffectedRows() == 1) {
                return (object)array("name" => $result["Nome"], "email" => $result["Email"], "date" => $result["Data"],
                    "startTime" => $result["OraInizio"], "endTime" => $result["OraFine"]);
            } else {
                throw \SessionException::moreAppointmentWithSameSessionId();
            }
        }
        return false;
    }

    /**
     * @throws DatabaseException
     * @throws \SessionException
     */
    public static function fetchAppointmentInfoBySessionID(Database $db, $sessionId) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT Cliente.Nome, Cliente.Email, DATE_FORMAT(Appuntamento.Data, "%e/%c/%Y") AS Data, TIME_FORMAT(Appuntamento.OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(Appuntamento.OraFine, "%H:%i") AS OraFine FROM Appuntamento, Cliente WHERE (Appuntamento.SessionId = ? AND Appuntamento.Cliente_id = Cliente.id)';
        $status = $db->query($sql, "s", $sessionId);
        if ($status) {
            //Success, we should find only one appointment
            $result = $db->getResult();
            if ($db->getAffectedRows() == 1) {
                // get the first array
                $result = $result[0];
                return (object)array("name" => $result["Nome"], "email" => $result["Email"], "date" => $result["Data"],
                    "startTime" => $result["OraInizio"], "endTime" => $result["OraFine"]);
            } else {
                throw \SessionException::moreAppointmentWithSameSessionId();
            }
        }
        return false;
    }
}