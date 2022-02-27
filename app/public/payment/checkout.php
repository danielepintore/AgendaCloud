<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__, 3)) . '/config/config.php';

if (isset($_POST['serviceId']) && is_numeric($_POST['serviceId']) && isset($_POST['date']) &&
    isset($_POST['employeeId']) && is_numeric($_POST['employeeId']) && isset($_POST['slot']) &&
    isset($_POST['clientNome']) && isset($_POST['clientCognome']) && isset($_POST['clientEmail']) &&
    isset($_POST['clientPhone']) && isset($_POST['paymentMethod']) && is_numeric($_POST['paymentMethod'])) {
    // the first thing to do is to check if the date is valid
    try {
        DateCheck::isValidDate($_POST['date']);
    } catch (DataException | Exception $e) {
        header("HTTP/1.1 303 See Other");
        header("Location: /error.php");
        die(0);
    }
    $client = new Client($_POST['clientNome'], $_POST['clientCognome'], $_POST['clientEmail'], $_POST['clientPhone']);
    try {
        $service = new Service($_POST['serviceId']);
    } catch (DatabaseException | Exception $e) {
        header("HTTP/1.1 303 See Other");
        header("Location: /error.php");
        die(0);
    }
    // we need to check if the selected payment method is cash or credit cart
    if (Payment::isAValidMethod($_POST['paymentMethod']) && Payment::isCashSelected($_POST['paymentMethod'])) {
        // valid payment method, cash selected
        // now we need to make the appointment as booked
        $appointment = new Appointment($_POST['serviceId'], $_POST['employeeId'], $_POST['date'], $_POST['slot'], $client, "Contanti", $_POST['paymentMethod'], "Waiting merchant approval");
        try {
            // make the reservation
            $bookResponse = $appointment->book();
            // the slot is reserved
            // TODO send email to the merchant
            // redirect
            header("HTTP/1.1 303 See Other");
            header("Location: " . $config['urls']['baseUrl'] . "/payment/success.php?paymentMethod=" . $_POST['paymentMethod']);
            die(0);
        } catch (DatabaseException | SlotException | Exception $e) {
            header("HTTP/1.1 303 See Other");
            header("Location: /error.php");
            die(0);
        }
    } elseif (Payment::isAValidMethod($_POST['paymentMethod'])) {
        // valid payment method, credit card selected
        // check price
        if ($service->getCost() * 100 < 50) {
            $price = 50;
        } else {
            $price = $service->getCost() * 100;
        }
        // This is a public sample test API key.
        // Don’t submit any personally identifiable information in requests made with this key.
        // Sign in to see your own test API key embedded in code samples.
        \Stripe\Stripe::setApiKey($config['stripe']['secret_api_key']);

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
                'success_url' => $domain . '/payment/success.php?sessionId={CHECKOUT_SESSION_ID}&paymentMethod=' . $_POST['paymentMethod'],
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
                'success_url' => $domain . '/payment/success.php?sessionId={CHECKOUT_SESSION_ID}&paymentMethod=' . $_POST['paymentMethod'],
                'cancel_url' => $domain,
            ]);
        }

        // now we need to make the appointment as booked
        $appointment = new Appointment($_POST['serviceId'], $_POST['employeeId'], $_POST['date'], $_POST['slot'], $client, $checkout_session->id, $_POST['paymentMethod'], "Pending payment");
        try {
            $bookResponse = $appointment->book();
            // the slot is reserved
            // redirect
            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
            die(0);
        } catch (DatabaseException | SlotException | Exception $e) {
            header("HTTP/1.1 303 See Other");
            header("Location: /error.php");
            die(0);
        }
    } else {
        // invalid payment method, quit
        header("HTTP/1.1 303 See Other");
        header("Location: /error.php");
        die(0);
    }
} else {
    header("HTTP/1.1 303 See Other");
    header("Location: /error.php");
    die(0);
}