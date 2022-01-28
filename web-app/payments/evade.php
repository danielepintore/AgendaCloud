<?php
require '../vendor/autoload.php';
require '../api/utils.php';
// Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/apikeys
\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_API_KEY']);

function print_log($val) {
return file_put_contents('php://stderr', print_r($val, TRUE));
}

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $_ENV['STRIPE_ENDPOINT_SECRET'];

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
$event = \Stripe\Webhook::constructEvent(
$payload, $sig_header, $endpoint_secret
);
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

function complete_order($session) {
    print_log($session);
    //success_payment($session->)
}

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    // Fulfill the purchase...
    // Change DB order status
    complete_order($session);
}
http_response_code(200);
exit();
