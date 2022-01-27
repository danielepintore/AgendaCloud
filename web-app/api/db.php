<?php
define('DB_SERVER', '127.0.0.1'); // TODO: change it
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'root');
define('DB_DATABASE', 'agenda-cloud');
define("DEBUG", false); //TODO: change this in production

if (DEBUG) {
error_reporting(E_ALL);
} else {
error_reporting(0);
}

function getDB() {
$db = new mysqli(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
if ($db->connect_errno){
throw new ErrorException("Errore con la connessione al database, contatta l'assistenza.");
}
return $db;
}