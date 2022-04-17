<?php

class Database {

    public $db;

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

    public function __destruct() {
        $this->db->close();
    }

}