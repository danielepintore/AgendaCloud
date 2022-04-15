<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';

// Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/apikeys
$config = Config::getConfig();
\Stripe\Stripe::setApiKey($config->stripe->secret_api_key);

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $config->stripe->endpoint_secret;

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

$event = null;
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    die(0);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    die(0);
}

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
    // Fulfill the purchase...
    // Change DB order status
    try {
        // mark order as paid
        Order::markAsPaid($session->id);
        // get appointment info
        $appointment = \Admin\Appointment::fetchAppointmentInfoBySessionID($session->id);
        // send email to the customer
        $body = MailClient::getConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
        $altBody = MailClient::getAltConfirmOrderMail($appointment->name, $appointment->date, $appointment->startTime, $appointment->endTime);
        MailClient::addMailToQueue("La tua prenotazione", $body, $altBody, $session->customer_details->email, $appointment->name);
    } catch (DatabaseException | Exception $e) {
        // send email to supervisor if there are any problems
        $body = $e->getMessage() . ": " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString() . "\n" . $e->getCode() . "\n" . $session;
        $phpMailer = new MailClient();
        $phpMailer->sendEmail("There are problems sending emails to customers", $body, $body, $config->mail->supervisor);
    }
}
http_response_code(200);
exit();
