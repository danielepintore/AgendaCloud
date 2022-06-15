<?php

/**
 * Handles the connection to the database
 */
class Database {

    public mysqli $db;
    private bool $isResultAvailabe;
    private mysqli_stmt|false $lastQueryStmt;

    /**
     * @throws DatabaseException
     * Creates the database object
     */
    public function __construct() {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        try {
            $this->db = new mysqli($config->db->host, $config->db->username, $config->db->password, $config->db->dbname);
            if ($this->db->connect_errno) {
                throw DatabaseException::connectionFailed();
            }
        } catch (Exception $e) {
            $textBody = Debug::getDebugMessage($e);
            try {
                //TODO find another method to warn me
                $phpMailer = new MailClient();
                $phpMailer->sendEmail("Errore nella connessione con il DB", $textBody, $textBody, $config->mail->supervisor);
            } catch (Exception $e) {
                if (DEBUG) {
                    Debug::printException($e);
                }
            }
            throw DatabaseException::connectionFailed();
        }
    }

    /**
     * @param string $sql
     * @param string|null $format
     * @param ...$args
     * @return bool
     * @throws DatabaseException
     * This function is a wrapper for executing queries. All checks of the input are performed by the
     * internal functions of mysqli
     */
    public function query(string $sql, string $format = null, ...$args): bool {
        $stmt = $this->db->prepare($sql);
        // check if the query have some errors
        if (!$stmt) {
            $this->isResultAvailabe = false;
            throw DatabaseException::queryPrepareFailed();
        }
        // if we have parameters
        if (!empty($format)) {
            // bind the parameters of the query and then execute the query
            if (!$stmt->bind_param($format, ...$args)) {
                $this->isResultAvailabe = false;
                throw DatabaseException::bindingParamsFailed();
            }
        }
        if (!$stmt->execute()) {
            $this->isResultAvailabe = false;
            throw DatabaseException::queryExecutionFailed();
        }
        // Success
        $this->isResultAvailabe = true;
        $this->lastQueryStmt = $stmt;
        return true;
    }

    /**
     * @return array
     * @throws DatabaseException
     * Gets the result from the query
     */
    public function getResult(): array {
        if ($this->isResultAvailabe) {
            $stmt_result = $this->lastQueryStmt->get_result();
            $this->isResultAvailabe = false;
            return $stmt_result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw DatabaseException::noResultAvailable();
        }
    }

    /**
     * @return int|string
     * Gets the number of affected rows
     */
    public function getAffectedRows(): int|string {
        return $this->lastQueryStmt->affected_rows;
    }

    /**
     * @return int|string
     * Gets the last insert id
     */
    public function getInsertId(): int|string {
        return $this->lastQueryStmt->insert_id;
    }

    /**
     * Close the database connection
     */
    public function __destruct() {
        $this->db->close();
    }

}