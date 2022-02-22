<?php

class Database {
    /**
     * @return mysqli
     * @throws DatabaseException
     */
    public static function getDB() {
        require realpath(dirname(__FILE__, 3)) . '/config/config.php';
        try {
            $db = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['dbname']);
            if ($db->connect_errno) {
                throw DatabaseException::connectionFailed();
            }
        } catch (Exception $e){
            throw DatabaseException::connectionFailed();
        }
        return $db;
    }
}