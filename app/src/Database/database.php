<?php

class Database {

    public $db;
    private $isResultAvailabe;
    private $lastQueryStmt;

    /**
     * @throws DatabaseException
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
            $textBody = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
            try {
                $phpMailer = new MailClient();
                $phpMailer->sendEmail("Errore nella connessione con il DB", $textBody, $textBody, $config->mail->supervisor);
            } catch (Exception $e) {
            }
            throw DatabaseException::connectionFailed();
        }
    }

    /**
     * @param string $sql
     * @param string $format
     * @param ...$args
     * @return bool
     * @throws DatabaseException
     * This function is a wrapper for executing queries all checks of the input are performed by the
     * internal functions
     */
    public function query(string $sql, string $format = null, ...$args){
        $stmt = $this->db->prepare($sql);
        // check if the query have some errors
        if ($stmt) {
            // check if we have parameters
            if (!empty($format)){
                // bind the parameters of the query and then execute the query
                if ($stmt->bind_param($format, ...$args)) {
                    if ($stmt->execute()) {
                        //Success
                        $this->isResultAvailabe = true;
                        $this->lastQueryStmt = $stmt;
                        return true;
                    } else {
                        $this->isResultAvailabe = false;
                        throw DatabaseException::queryExecutionFailed();
                    }
                } else {
                    $this->isResultAvailabe = false;
                    throw DatabaseException::bindingParamsFailed();
                }
            } else {
                // execute the query
                if ($stmt->execute()) {
                    //Success
                    $this->isResultAvailabe = true;
                    $this->lastQueryStmt = $stmt;
                    return true;
                } else {
                    $this->isResultAvailabe = false;
                    throw DatabaseException::queryExecutionFailed();
                }
            }
        } else {
            $this->isResultAvailabe = false;
            throw DatabaseException::queryPrepareFailed();
        }
    }

    public function getResult() {
        if ($this->isResultAvailabe){
            $stmt_result = $this->lastQueryStmt->get_result();
            $this->isResultAvailabe = false;
            return $stmt_result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw DatabaseException::noResultAvailable();
        }
    }

    public function getAffectedRows(){
            return $this->lastQueryStmt->affected_rows;
    }

    public function getInsertId(){
        return $this->lastQueryStmt->insert_id;
    }

    public function __destruct() {
        $this->db->close();
    }

}