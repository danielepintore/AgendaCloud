<?php
namespace Admin;

use Database;
use DatabaseException;

class Services {
    /**
     * @throws DatabaseException
     */
    public static function getEmployeeService($isAdmin, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if ($isAdmin) {
            $db = Database::getDB();
            $sql = "SELECT id, Nome, Durata, OraInizio, OraFine, Costo FROM Servizio WHERE(IsActive = TRUE)";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        } else {
            $db = Database::getDB();
            $sql = "SELECT Servizio.id, Servizio.Nome, Servizio.Durata, Servizio.OraInizio, Servizio.OraFine, Servizio.Costo FROM Servizio, Offre WHERE (Offre.Dipendente_id = ? AND Offre.Servizio_id = Servizio.id)";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw DatabaseException::queryPrepareFailed();
            }
            if (!$stmt->bind_param('i', $employeeId)) {
                throw DatabaseException::bindingParamsFailed();
            }
            if ($stmt->execute()) {
                //Success
                $result = $stmt->get_result();
                $response = array();
                foreach ($result as $r) {
                    $response[] = array('id' => $r['id'], 'Nome' => $r['Nome'], 'Durata' => $r['Durata'],
                        'OraInizio' => $r['OraInizio'], 'OraFine' => $r['OraFine'], 'Costo' => $r['Costo']);
                }
                return $response;
            } else {
                throw DatabaseException::queryExecutionFailed();
            }
        }
    }
}