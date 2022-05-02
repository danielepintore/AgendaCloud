<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailClient {
    private $senderAddress;
    private $username;
    private $password;
    private $smtpHostname;
    private $smtpPort;
    private $phpMailer;
    private $active;


    /**
     * @param $senderAddress
     * @param $username
     * @param $password
     * @param $smtpHostname
     * @param $smtpPort
     */

    public function __construct() {
        $config = Config::getConfig();
        $this->senderAddress = $config->mail->sender;
        $this->username = $config->mail->username;
        $this->password = $config->mail->password;
        $this->smtpHostname = $config->mail->hostname;
        $this->smtpPort = $config->mail->port;
        $this->phpMailer = $this->startPhpMailer();
        if ($this->phpMailer == null) {
            $this->active = false;
        } else {
            $this->active = true;
        }
    }

    private function startPhpMailer() {
        //Create an instance; passing `true` enables exceptions
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        $phpMailer = new PHPMailer(true);
        try {
            //Server settings
            if (DEBUG) {
                $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            } else {
                $phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
            }
            $phpMailer->isSMTP();                                            //Send using SMTP
            $phpMailer->Host = $this->smtpHostname;                    //Set the SMTP server to send through
            $phpMailer->SMTPAuth = true;                                   //Enable SMTP authentication
            $phpMailer->Username = $this->username;                     //SMTP username
            $phpMailer->Password = $this->password;                               //SMTP password
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable TLS encryption
            $phpMailer->Port = $this->smtpPort;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $phpMailer->setFrom($this->senderAddress, $config->company->name);
            return $phpMailer;
        } catch (Exception $e) {
            if (DEBUG) {
                print("Message could not be sent. Mailer Error: {$phpMailer->ErrorInfo}");
            }
            return null;
        }
    }

    /*
     * Send email function wrapper return true if the email is sented otherwise it starts an exception
     */
    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws MailException
     */
    public function sendEmail($subject, $body, $altBody, $toAddress, $toName = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        if ($this->active) {
            try {
                if (empty($toName)) {
                    $this->phpMailer->addAddress($toAddress);     //Add a recipient
                } else {
                    $this->phpMailer->addAddress($toAddress, $toName);     //Add a recipient
                }
                $this->phpMailer->addReplyTo($config->mail->company, $config->company->name);

                //Content
                $this->phpMailer->isHTML(true);                                  //Set email format to HTML
                $this->phpMailer->Subject = $subject;
                $this->phpMailer->Body = $body;                               // Html body
                $this->phpMailer->AltBody = $altBody;                            //This is the body in plain text for non-HTML mail clients

                $this->phpMailer->send();
                return true;
            } catch (Exception $e) {
                throw MailException::failedToSend();
            }
        } else {
            throw MailException::failedToSend();
        }
    }

    /**
     * @throws DatabaseException
     */
    public static function addMailToQueue(Database $db, $subject, $body, $altBody, $toAddress, $toName = null) {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($toName)){
            $sql = 'INSERT INTO EmailQueue(id, subject, body, altBody, destination) VALUES (NULL, ?, ?, ?, ?)';
        } else {
            $sql = 'INSERT INTO EmailQueue(id, subject, body, altBody, destination, receiverName) VALUES (NULL, ?, ?, ?, ?, ?)';
        }
        if (empty($toName)) {
            $status = $db->query($sql, "ssss", $subject, $body, $altBody, $toAddress);
        } else {
            $status = $db->query($sql, "sssss", $subject, $body, $altBody, $toAddress, $toName);
        }
        if ($status) {
            //Success
            return true;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }

    public static function getConfirmOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $body = '
        <!DOCTYPE html>
        <html>
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                <style type="text/css">
                    @media screen {
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 400;
                            src: local("Lato Regular"), local("Lato - Regular"), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 700;
                            src: local("Lato Bold"), local("Lato - Bold"), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 400;
                            src: local("Lato Italic"), local("Lato - Italic"), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 700;
                            src: local("Lato Bold Italic"), local("Lato - BoldItalic"), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format("woff");
                        }
                    }
            
                    /* CLIENT-SPECIFIC STYLES */
                    body,
                    table,
                    td,
                    a {
                        -webkit-text-size-adjust: 100%%;
                        -ms-text-size-adjust: 100%%;
                    }
            
                    table,
                    td {
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                    }
            
                    img {
                        -ms-interpolation-mode: bicubic;
                    }
            
                    /* RESET STYLES */
                    img {
                        border: 0;
                        height: auto;
                        line-height: 100%%;
                        outline: none;
                        text-decoration: none;
                    }
            
                    table {
                        border-collapse: collapse !important;
                    }
            
                    body {
                        height: 100%% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100%% !important;
                    }
            
                    /* iOS BLUE LINKS */
                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }
            
                    /* MOBILE STYLES */
                    @media screen and (max-width: 600px) {
                        h1 {
                            font-size: 32px !important;
                            line-height: 32px !important;
                        }
                    }
            
                    /* ANDROID CENTER FIX */
                    div[style*="margin: 16px 0;"] {
                        margin: 0 !important;
                    }
                </style>
            </head>
            
            <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
                <!-- HIDDEN PREHEADER TEXT -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%%">
                    <!-- LOGO -->
                    <tr>
                        <td bgcolor="#008759" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#008759" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#ffffff" align="center" valign="top"
                                        style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                        <h1 style="font-size: 36px; font-weight: 400; margin-top: 2; margin-bottom: 0">%s,</h1>
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 0; margin-bottom: 2;">grazie per l\'appuntamento!!</h1>
                                        <img src="data:image/png;base64,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" width="125" height="125"
                                             style="display: block; border: 0px;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px; margin-bottom: 4rem;">
                                <tr>
                                    <td bgcolor="#ffffff" align="left"
                                        style="padding: 20px 30px 40px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px; border-radius: 0px 0px 4px 4px">
                                        <p style="margin: 0;"> Il tuo appuntamento del %s dalle %s alle %s &egrave; appena stato
                                            confermato </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html> ';
        return sprintf($body, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    public static function getAltConfirmOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $altBody = "%s, grazie per la prenotazione. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata confermata";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    public static function getRejectOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $body = '
        <!DOCTYPE html>
        <html>
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                <style type="text/css">
                    @media screen {
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 400;
                            src: local("Lato Regular"), local("Lato - Regular"), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 700;
                            src: local("Lato Bold"), local("Lato - Bold"), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 400;
                            src: local("Lato Italic"), local("Lato - Italic"), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 700;
                            src: local("Lato Bold Italic"), local("Lato - BoldItalic"), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format("woff");
                        }
                    }
            
                    /* CLIENT-SPECIFIC STYLES */
                    body,
                    table,
                    td,
                    a {
                        -webkit-text-size-adjust: 100%%;
                        -ms-text-size-adjust: 100%%;
                    }
            
                    table,
                    td {
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                    }
            
                    img {
                        -ms-interpolation-mode: bicubic;
                    }
            
                    /* RESET STYLES */
                    img {
                        border: 0;
                        height: auto;
                        line-height: 100%%;
                        outline: none;
                        text-decoration: none;
                    }
            
                    table {
                        border-collapse: collapse !important;
                    }
            
                    body {
                        height: 100%% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100%% !important;
                    }
            
                    /* iOS BLUE LINKS */
                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }
            
                    /* MOBILE STYLES */
                    @media screen and (max-width: 600px) {
                        h1 {
                            font-size: 32px !important;
                            line-height: 32px !important;
                        }
                    }
            
                    /* ANDROID CENTER FIX */
                    div[style*="margin: 16px 0;"] {
                        margin: 0 !important;
                    }
                </style>
            </head>
            
            <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
                <!-- HIDDEN PREHEADER TEXT -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%%">
                    <!-- LOGO -->
                    <tr>
                        <td bgcolor="#dc3545" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#dc3545" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#ffffff" align="center" valign="top"
                                        style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 2; margin-bottom: 0">%s,</h1>
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 0; margin-bottom: 2;">il tuo appuntamento &egrave; stato rifiutato</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px; margin-bottom: 4rem;">
                                <tr>
                                    <td bgcolor="#ffffff" align="left"
                                        style="padding: 20px 30px 40px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px; border-radius: 0px 0px 4px 4px">
                                        <p style="margin: 0;">Ci dispiace comunicarti che il tuo appuntamento del %s dalle %s alle %s &egrave; stato rifiutato. Rispondi a questa mail se vuoi sapere il perch&eacute;</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html> ';
        return sprintf($body, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    public static function getAltRejectOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $altBody = "%s, la tua prenotazione è stata rifiutata. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata rifiutata. Rispondi a questa mail per ottenere altre informazioni";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    public static function getDeleteOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $body = '
        <!DOCTYPE html>
        <html>
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                <style type="text/css">
                    @media screen {
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 400;
                            src: local("Lato Regular"), local("Lato - Regular"), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: normal;
                            font-weight: 700;
                            src: local("Lato Bold"), local("Lato - Bold"), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 400;
                            src: local("Lato Italic"), local("Lato - Italic"), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format("woff");
                        }
            
                        @font-face {
                            font-family: "Lato";
                            font-style: italic;
                            font-weight: 700;
                            src: local("Lato Bold Italic"), local("Lato - BoldItalic"), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format("woff");
                        }
                    }
            
                    /* CLIENT-SPECIFIC STYLES */
                    body,
                    table,
                    td,
                    a {
                        -webkit-text-size-adjust: 100%%;
                        -ms-text-size-adjust: 100%%;
                    }
            
                    table,
                    td {
                        mso-table-lspace: 0pt;
                        mso-table-rspace: 0pt;
                    }
            
                    img {
                        -ms-interpolation-mode: bicubic;
                    }
            
                    /* RESET STYLES */
                    img {
                        border: 0;
                        height: auto;
                        line-height: 100%%;
                        outline: none;
                        text-decoration: none;
                    }
            
                    table {
                        border-collapse: collapse !important;
                    }
            
                    body {
                        height: 100%% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100%% !important;
                    }
            
                    /* iOS BLUE LINKS */
                    a[x-apple-data-detectors] {
                        color: inherit !important;
                        text-decoration: none !important;
                        font-size: inherit !important;
                        font-family: inherit !important;
                        font-weight: inherit !important;
                        line-height: inherit !important;
                    }
            
                    /* MOBILE STYLES */
                    @media screen and (max-width: 600px) {
                        h1 {
                            font-size: 32px !important;
                            line-height: 32px !important;
                        }
                    }
            
                    /* ANDROID CENTER FIX */
                    div[style*="margin: 16px 0;"] {
                        margin: 0 !important;
                    }
                </style>
            </head>
            
            <body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
                <!-- HIDDEN PREHEADER TEXT -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%%">
                    <!-- LOGO -->
                    <tr>
                        <td bgcolor="#dc3545" align="center">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td align="center" valign="top" style="padding: 40px 10px 40px 10px;"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#dc3545" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px;">
                                <tr>
                                    <td bgcolor="#ffffff" align="center" valign="top"
                                        style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 2; margin-bottom: 0">%s,</h1>
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 0; margin-bottom: 2;">il tuo appuntamento &egrave; stato cancellato</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%%" style="max-width: 600px; margin-bottom: 4rem;">
                                <tr>
                                    <td bgcolor="#ffffff" align="left"
                                        style="padding: 20px 30px 40px 30px; color: #666666; font-family: \'Lato\', Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px; border-radius: 0px 0px 4px 4px">
                                        <p style="margin: 0;">Ci dispiace comunicarti che il tuo appuntamento del %s dalle %s alle %s &egrave; stato cancellato. Rispondi a questa mail se vuoi sapere il perch&eacute;</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html> ';
        return sprintf($body, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    public static function getAltDeleteOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime) {
        $altBody = "%s, la tua prenotazione è stata cancellata. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata cancellata. Rispondi a questa mail per ottenere altre informazioni";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }
}