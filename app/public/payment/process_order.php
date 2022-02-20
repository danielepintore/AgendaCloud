<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__, 3)) . '/config/config.php';

// Set your secret key. Remember to switch to your live secret key in production.
// See your keys here: https://dashboard.stripe.com/apikeys
\Stripe\Stripe::setApiKey($config['stripe']['secret_api_key']);

// You can find your endpoint's secret in your webhook settings
$endpoint_secret = $config['stripe']['endpoint_secret'];

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

$event = null;
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    http_response_code(400);
    exit();
}

// Handle the checkout.session.completed event
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;
    // Fulfill the purchase...
    // Change DB order status
    Order::markAsPaid($session->id);

}
http_response_code(200);
exit();
