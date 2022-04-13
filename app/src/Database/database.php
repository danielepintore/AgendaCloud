<?php

class Database {
    /**
     * @return mysqli
     * @throws DatabaseException
     */
    public static function getDB() {
        $config = Config::getConfig();
        try {
            $db = new mysqli($config->db->host, $config->db->username, $config->db->password, $config->db->dbname);
            if ($db->connect_errno) {
                throw DatabaseException::connectionFailed();
            }
        } catch (Exception $e){
            $textBody = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
            try {
                $phpMailer = new MailClient();
                $phpMailer->sendEmail("Errore nella connessione con il DB", $textBody, $textBody, $config->mail->supervisor);
            } catch (Exception $e) { }
            throw DatabaseException::connectionFailed();
        }
        return $db;
    }
}