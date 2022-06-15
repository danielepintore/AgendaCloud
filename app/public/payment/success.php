<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();

if (isset($_GET["sessionId"]) && isset($_GET['paymentMethod']) && $_GET['paymentMethod'] == CREDIT_CARD) {
    try {
        $session = new Session($config->stripe->secret_api_key);
        $customer = $session->getCustomerData($_GET["sessionId"]);
        $method = CREDIT_CARD;
    } catch (PaymentException | Exception $e) {
        if (DEBUG){
            Debug::printException($e);
        }
        $method = CASH;
    }
} elseif (isset($_GET['paymentMethod']) && $_GET['paymentMethod'] == CASH) {
    $method = CASH;
} else {
    header("HTTP/1.1 303 See Other");
    header("Location: " . '/error.php');
    exit(0);
}
?>
<html>
<head>
    <title><?php print("Conferma dell'appuntamento - ".$config->company->name." - AgendaCloud");?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/status-page.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container-fluid d-flex align-items-center justify-content-center h-100">
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-circle-check" id="success-img"></i>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <?php if ($method == CREDIT_CARD) { ?>
                        <p class="card-text">Grazie <?php print($customer->name) ?>! a breve riceverai una mail di
                            conferma all'indirizzo: <em><?php print($customer->email) ?></em></p>
                    <?php } else if ($method == CASH) { ?>
                        <p class="card-text">Grazie! riceverai una mail di conferma quando la tua prenotazione sarà
                            confermata dal commerciante</p>
                    <?php } ?>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="/">
                        <button type="button" id="home-btn" class="btn btn-success">Nuova prenotazione</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>