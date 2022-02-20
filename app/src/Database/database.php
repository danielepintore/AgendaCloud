<?php
class Database {
    public static function getDB() {
        require realpath(dirname(__FILE__, 3)) . '/config/config.php';
        $db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['dbname']);
        if ($db->connect_errno){
            throw new ErrorException("Errore con la connessione al database, contatta l'assistenza.");
        }
        return $db;
    }
}