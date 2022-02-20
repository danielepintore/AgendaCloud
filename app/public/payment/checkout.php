<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__, 3)) . '/config/config.php';

if (isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date']) &&
    isset($_POST['workerId']) && is_numeric($_POST['workerId']) && isset($_POST['slot']) &&
    isset($_POST['clientNome']) && isset($_POST['clientCognome']) && isset($_POST['clientEmail']) && isset($_POST['clientPhone'])){
    $client = new Client($_POST['clientNome'], $_POST['clientCognome'], $_POST['clientEmail'], $_POST['clientPhone']);
    $service = new Service($_POST['serviceId']);

    // check price
    if($service->getCost() * 100 < 50) {
        $price = 50;
    } else {
        $price = $service->getCost() * 100;
    }
    // This is a public sample test API key.
    // Don’t submit any personally identifiable information in requests made with this key.
    // Sign in to see your own test API key embedded in code samples.
    \Stripe\Stripe::setApiKey($config['stripe']['secret_api_key']);

    //header('Content-Type: application/json');

    $domain = $config['urls']['baseUrl'];

    // check if the service have an image to display
    if ($service->getImageUrl() != null) {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'name' => $service->getName(),
                'description' => $service->getDescription(),
                'images' => [$service->getImageUrl()],
                'amount' => $price,
                'currency' => 'eur',
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $domain . '/payment/success.php?sessionId={CHECKOUT_SESSION_ID}',
            'cancel_url' => $domain,
        ]);
    } else {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'name' => $service->getName(),
                'description' => $service->getDescription(),
                'amount' => $price,
                'currency' => 'eur',
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $domain . '/payment/success.php?sessionId={CHECKOUT_SESSION_ID}',
            'cancel_url' => $domain,
        ]);
    }

    // now we need to make the appointment as booked
    $appointment = new Appointment($_POST['serviceId'], $_POST['workerId'], $_POST['date'], $_POST['slot'], $client, $checkout_session->id, "Pending Payment");
    $bookResponse = $appointment->book();
    // se non ci sono stati errori fornisci la risposta
    if (!$bookResponse["error"]) {
        // the slot is reserved
        // redirect
        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
        exit();
    } else {
        print $bookResponse["info"];
        //TODO: redirect to error page
        exit();
    }
} else {
    //TODO: redirect to error page
    exit();
}