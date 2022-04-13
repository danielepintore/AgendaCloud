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
        if ($this->phpMailer == null){
            $this->active = false;
        } else {
            $this->active = true;
        }
    }

    private function startPhpMailer(){
        //Create an instance; passing `true` enables exceptions
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        $phpMailer = new PHPMailer(true);
        try {
            //Server settings
            $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $phpMailer->isSMTP();                                            //Send using SMTP
            $phpMailer->Host       = $this->smtpHostname;                    //Set the SMTP server to send through
            $phpMailer->SMTPAuth   = true;                                   //Enable SMTP authentication
            $phpMailer->Username   = $this->username;                     //SMTP username
            $phpMailer->Password   = $this->password;                               //SMTP password
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable TLS encryption
            $phpMailer->Port       = $this->smtpPort;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $phpMailer->setFrom($this->senderAddress, $config->company->name);
            return $phpMailer;
        } catch (Exception $e) {
            if (DEBUG){
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
    public function sendEmail($subject, $body, $altBody, $toAddress, $toName = null){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $config = Config::getConfig();
        if ($this->active){
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
            } catch (Exception $e){
                throw MailException::failedToSend();
            }
        } else {
            throw MailException::failedToSend();
        }
    }

}