<?php
/**
 * This script need to be called with a cronjob every 1 minute so all pending sessions can be deleted
 * Example cronjob call "php app/resources/remove_expired_sessions.php * * * * *  /usr/bin/php app/resources/remove_expired_sessions.php >/dev/null 2>&1" without quotes
 * connect to the database (with a specific limited) and get the list of sessions that should be deleted
 */
require_once(realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php');

$config = Config::getConfig();
// connect to database
if (!empty($config->stripe->secret_api_key) && !empty($config->stripe->endpoint_secret)) {
    try {
        $db = new mysqli($config->db->host, $config->db->expire_user, $config->db->expire_pwd, $config->db->dbname);
        if ($db->connect_errno) {
            throw DatabaseException::connectionFailed();
        }
        // get all the sessions id, needed to call the expire() method on them
        $sql = 'SELECT SessionId FROM Appuntamento WHERE (UNIX_TIMESTAMP() - AddedAt >' . $config->stripe->session_timeout . '* 60 AND Stato =' . PAYMENT_PENDING . ')';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($stmt->execute()) {
            //Success
            $sessionIds = $stmt->get_result();
            $session = new Session($config->stripe->secret_api_key);
            $session->invalidateSessions($sessionIds);
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
        // delete all the sessions that are in pending status and make this slots available again
        $sql = 'DELETE FROM Appuntamento WHERE (UNIX_TIMESTAMP() - AddedAt >' . $config->stripe->session_timeout . '* 60 AND Stato = ' . PAYMENT_PENDING . ')';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if ($stmt->execute()) {
            die(0);
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    } catch (DatabaseException|PaymentException|Exception $e) {
        $textBody = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
        try {
            $phpMailer = new MailClient();
            $phpMailer->sendEmail("Errore nella rimossione delle sessioni scadute", $textBody, $textBody, $config->mail->supervisor);
        } catch (Exception $e) {
        }
    }
}