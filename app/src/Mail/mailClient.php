<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailClient {
    private string $senderAddress;
    private string $username;
    private string $password;
    private string $smtpHostname;
    private int $smtpPort;
    private mixed $phpMailer;
    private bool $active;


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

    /**
     * @return PHPMailer|null
     * Initialize the PHPMailer object if there are some problems it returns null
     */
    private function startPhpMailer() {
        //Create an instance; passing `true` enables exceptions
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        $phpMailer = new PHPMailer(true);
        try {
            //Server settings
            if (DEBUG) {
                $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;             //Enable verbose debug output
            } else {
                $phpMailer->SMTPDebug = SMTP::DEBUG_OFF;
            }
            $phpMailer->isSMTP();                                       //Send using SMTP
            $phpMailer->Host = $this->smtpHostname;                     //Set the SMTP server to send through
            $phpMailer->SMTPAuth = true;                                //Enable SMTP authentication
            $phpMailer->Username = $this->username;                     //SMTP username
            $phpMailer->Password = $this->password;                     //SMTP password
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;    //Enable TLS encryption
            $phpMailer->Port = $this->smtpPort;                         //TCP port to connect to; use 587 if you have
                                                                        // set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

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

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @throws MailException
     * Send the email, return true on success, otherwise starts an exception
     */
    public function sendEmail($subject, $body, $altBody, $toAddress, $toName = null): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        if (!$this->active) {
            throw MailException::failedToSend();
        }
        try {
            if (empty($toName)) {
                $this->phpMailer->addAddress($toAddress);              //Add a recipient without name
            } else {
                $this->phpMailer->addAddress($toAddress, $toName);     //Add a recipient with name
            }
            // Add the reply to address
            $this->phpMailer->addReplyTo($config->mail->company, $config->company->name);
            // Mail content
            $this->phpMailer->isHTML(true);                      //Set email format to HTML
            $this->phpMailer->Subject = $subject;
            $this->phpMailer->Body = $body;                             // Html body
            $this->phpMailer->AltBody = $altBody;                       //This is the body in plain text for non-HTML mail clients
            $this->phpMailer->send();
            return true;
        } catch (Exception $e) {
            throw MailException::failedToSend();
        }
    }

    /**
     * @param Database $db
     * @param $subject
     * @param $body
     * @param $altBody
     * @param $toAddress
     * @param $toName
     * @return bool
     * @throws DatabaseException
     * Adds the email to the queue, in the database
     * Returns true if success otherwise it'll start an eception
     */
    public static function addMailToQueue(Database $db, $subject, $body, $altBody, $toAddress, $toName = null): bool {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        if (empty($toName)){
            $sql = 'INSERT INTO EmailQueue(id, subject, body, altBody, destination) VALUES (NULL, ?, ?, ?, ?)';
            $status = $db->query($sql, "ssss", $subject, $body, $altBody, $toAddress);
        } else {
            $sql = 'INSERT INTO EmailQueue(id, subject, body, altBody, destination, receiverName) VALUES (NULL, ?, ?, ?, ?, ?)';
            $status = $db->query($sql, "sssss", $subject, $body, $altBody, $toAddress, $toName);
        }
        if ($status) {
            // Success
            return true;
        } else {
            throw DatabaseException::queryExecutionFailed();
        }
    }

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the html body for the confirm appointment email
     */
    public static function getConfirmAppointmentMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
        $config = Config::getConfig();
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
                                        <img src="' . $config->urls->baseUrl . '/img/check.png" width="125" height="125"
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

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the alt version of the confirmation appointment email, useful for older mail client that doesn't support HTML
     */
    public static function getAltConfirmAppointmentMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
        $altBody = "%s, grazie per la prenotazione. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata confermata";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the html body for the reject appointment email
     */
    public static function getRejectAppointmentMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
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

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the alt version of the reject appointment email, useful for older mail client that doesn't support HTML
     */
    public static function getAltRejectOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
        $altBody = "%s, la tua prenotazione è stata rifiutata. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata rifiutata. Rispondi a questa mail per ottenere altre informazioni";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the html body for delete appointment email
     */
    public static function getDeleteOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
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
                                        <p style="margin: 0;">Ci dispiace comunicarti che il tuo appuntamento del %s dalle %s alle %s &egrave; stato cancellato. Se hai effettuato il pagamento con la carta di credito riceverai il rimborso nei prossimi giorni. Per sapere ulteriori informazioni riguardo l\'annullamento dell\'appuntamento</p>
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

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the alt version of delete appointment email, useful for older mail client that doesn't support HTML
     */
    public static function getAltDeleteOrderMail($name, $dateBooking, $bookingStartTime, $bookingEndTime): string {
        $altBody = "%s, la tua prenotazione è stata cancellata. Vogliamo avvisarti che la tua prenotazione per il %s dalle %s alle %s è stata cancellata. Se hai effettuato il pagamento con la carta di credito riceverai il rimborso nei prossimi giorni. Per sapere ulteriori informazioni riguardo l'annullamento dell'appuntamento non esitare a contattarci";
        return sprintf($altBody, $name, $dateBooking, $bookingStartTime, $bookingEndTime);
    }

    /**
     * @param $paymentIntent
     * @return string
     * Gets the html body for refund problem email
     */
    public static function getRefundProblemMail($paymentIntent): string {
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
                                        <h1 style="color:black; font-size: 36px; font-weight: 400; margin-top: 0; margin-bottom: 2;">Si è verificato un problema con l\'emmissione di un rimborso</h1>
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
                                        <p style="margin: 0;">Controlla la dashboard di stripe per verificare la situazione e in caso emetti il rimborso manualmente, codice identificativo del pagamento: %s</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
        </html> ';
        return sprintf($body, $paymentIntent);
    }

    /**
     * @param $name
     * @param $dateBooking
     * @param $bookingStartTime
     * @param $bookingEndTime
     * @return string
     * Gets the alt version of refund problem  email, useful for older mail client that doesn't support HTML
     */
    public static function getAltRefundProblemMail($paymentIntent): string {
        $altBody = "Si è verificato un problema con l\'emmissione di un rimborso. Controlla la dashboard di stripe per verificare la situazione e in caso emetti il rimborso manualmente, codice identificativo del pagamento: %s";
        return sprintf($altBody, $paymentIntent);
    }
}