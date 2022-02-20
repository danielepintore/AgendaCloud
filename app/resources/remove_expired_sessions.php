// This script need to be called with a cronjob every 1 minute so all pending sessions can be deleted
// Example cronjob call php app/resources/remove_expired_sessions.php
// * * * * *  /usr/bin/php app/resources/remove_expired_sessions.php >/dev/null 2>&1
// connect to the database (with a specific limited) and get the list of sessions that should be deleted
<?php
require_once realpath(dirname(__FILE__, 2)) . '/config/config.php';
require_once(realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php');

// connect to database
$db = new mysqli($config['db']['host'], $config['db']['expire_user'], $config['db']['expire_pwd'], $config['db']['dbname']);
if ($db->connect_errno){
    throw new ErrorException("Errore con la connessione al database, contatta l'assistenza.");
}

// get all the sessions id, needed to call the expire method on them
$sql = 'SELECT SessionId FROM Appuntamento WHERE (UNIX_TIMESTAMP() - AddedAt >'. $config['stripe']['session_timeout'] .'* 60 AND Stato = "Pending Payment")';
$stmt = $db->prepare($sql);
if ($stmt->execute()) {
    //Success
    $sessionIds = $stmt->get_result();
    $session = new Session($config['stripe']['secret_api_key']);
    $session->invalidateSessions($sessionIds);
} else {
    // TODO add logging stuff
}

// delete all the sessions that are in pending status and make this slots available again
$sql = 'DELETE FROM Appuntamento WHERE (UNIX_TIMESTAMP() - AddedAt >'. $config['stripe']['session_timeout'] .'* 60 AND Stato = "Pending Payment")';
$stmt = $db->prepare($sql);
if ($stmt->execute()) {
    //Success
    // TODO add logging stuff
} else {
    // TODO add logging stuff
}
