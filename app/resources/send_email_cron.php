// This script need to be called with a cronjob every 5/10 minutes
// Example cronjob call php app/resources/send_email_cron.php
// * * * * *  /usr/bin/php app/resources/send_email_cron.php >/dev/null 2>&1
<?php
require_once(realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php');
$config = Config::getConfig();

function updateMailStatus($mailId){
    try {
        $config = Config::getConfig();
        $db = new mysqli($config->db->host, $config->db->email_user, $config->db->email_pwd, $config->db->dbname);
        if ($db->connect_errno) {
            throw DatabaseException::connectionFailed();
        }
        // status == 0 means email not sended
        // status == 1 means email sended
        $sql = 'UPDATE EmailQueue SET status = 1 WHERE (id = ?)';
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw DatabaseException::queryPrepareFailed();
        }
        if (!$stmt->bind_param('i', $mailId)) {
            throw DatabaseException::bindingParamsFailed();
        }
        if ($stmt->execute()) {
            //Success
            return true;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    } catch (DatabaseException|Exception $e) {
        $textBody = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
        try {
            $phpMailer = new MailClient();
            $phpMailer->sendEmail("Errore nell'aggiornamento dello stato delle email nel database", $textBody, $textBody, $config->mail->supervisor);
        } catch (Exception $e) { }
    }
}

try {
    // connect to database
    $db = new mysqli($config->db->host, $config->db->email_user, $config->db->email_pwd, $config->db->dbname);
    if ($db->connect_errno) {
        throw DatabaseException::connectionFailed();
    }
    // status == 0 means email not sended
    // status == 1 means email sended
    $sql = 'SELECT * FROM EmailQueue WHERE (status = 0)';
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw DatabaseException::queryPrepareFailed();
    }
    if ($stmt->execute()) {
        //Success
        $phpMailer = new MailClient();
        $emails = $stmt->get_result();
        foreach ($emails as $email) {
            if (DEBUG){
                if (empty($email["receiverName"])){
                    $phpMailer->sendEmail($email["subject"], $email["body"], $email["altBody"], "example@email-blackhole.com");
                } else {
                    $phpMailer->sendEmail($email["subject"], $email["body"], $email["altBody"], "example@email-blackhole.com", $email["receiverName"]);
                }
                updateMailStatus($email["id"]);
            } else {
                if (empty($email["receiverName"])){
                    $phpMailer->sendEmail($email["subject"], $email["body"], $email["altBody"], $email["destination"]);
                } else {
                    $phpMailer->sendEmail($email["subject"], $email["body"], $email["altBody"], $email["destination"], $email["receiverName"]);
                }
                updateMailStatus($email["id"]);
            }
        }
    } else {
        throw DatabaseException::queryExecutionFailed();
    }
} catch (DatabaseException|Exception $e) {
    $textBody = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode();
    try {
        $phpMailer = new MailClient();
        $phpMailer->sendEmail("Errore nell'invio delle email presenti nella queue", $textBody, $textBody, $config->mail->supervisor);
    } catch (Exception $e) { }
}