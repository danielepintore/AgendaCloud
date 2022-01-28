<?php

require '../vendor/autoload.php';
require '../api/utils.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable('../'); //.env location file
$dotenv->load();
if (isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date']) &&
    isset($_POST['workerId']) && is_numeric($_POST['workerId']) && isset($_POST['slot']) &&
    isset($_POST['clientNome']) && isset($_POST['clientCognome']) && isset($_POST['clientEmail']) && isset($_POST['clientPhone'])){
    $client = array("nome" => $_POST['clientNome'], "cognome" => $_POST['clientCognome'], "email" => $_POST['clientEmail'], "phone" => $_POST['clientPhone']);
    $service_info = get_services($_POST['serviceId']);
    if (!$service_info["error"]) {
        if (count($service_info["response"]) == 0){
            // error no service with this id
        } else {
            //service found
            $service_info = $service_info['response'];
        }
    } else {
        // error finding the service
        //TODO trigger error
    }
    // check price
    if($service_info["Costo"]*100 < 50) {
        $service_info["Costo"] = 50;
    } else {
        $service_info["Costo"] = $service_info["Costo"] * 100;
    }
    // This is a public sample test API key.
    // Don’t submit any personally identifiable information in requests made with this key.
    // Sign in to see your own test API key embedded in code samples.
    \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_API_KEY']);

    header('Content-Type: application/json');

    $domain = $_ENV['DOMAIN'];

    $checkout_session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'name' => $service_info['Nome'],
            'description' => 'Comfortable cotton t-shirt',
            'images' => ['https://example.com/t-shirt.png'],
            'amount' => $service_info["Costo"],
            'currency' => 'eur',
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $domain . 'payments/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $domain,
    ]);

    // aggiungere lo slot
    $book = book($_POST['serviceId'], $_POST['workerId'], $_POST['date'], $_POST['slot'], $client, $checkout_session->id);
    // se non ci sono stati errori fornisci la risposta
    if (!$book["error"]) {
        // lo slot è prenotato
        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
        exit();
    } else {
        print $book["info"];
        //TODO: redirect to error page
        exit();
    }


} else {
    //TODO: redirect to error page
    exit();
}