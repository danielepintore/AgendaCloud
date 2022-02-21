<?php
require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
require_once realpath(dirname(__FILE__, 3)) . '/config/config.php';
$method = 0;
if (isset($_GET["sessionId"]) && isset($_GET['paymentMethod']) && $_GET['paymentMethod'] == 1) {
    try {
        $session = new Session($config['stripe']['secret_api_key']);
        $customer = $session->getCustomerData($_GET["sessionId"]);
        $method = 1;
    } catch (Exception $e) {
        header("HTTP/1.1 303 See Other");
        header("Location: " . $config['urls']['baseUrl'] . '/payment/fail.php');
        exit(0);
    }
} elseif (isset($_GET['paymentMethod']) && $_GET['paymentMethod'] == 2) {
    $method = 2;
} else {
    header("HTTP/1.1 303 See Other");
    header("Location: " . $config['urls']['baseUrl'] . '/payment/fail.php');
    exit(0);
}
?>
<html>
<head>
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
                    <?php if ($method == 1) { ?>
                        <p class="card-text">Grazie <?php print($customer->name) ?>! a breve riceverai una mail di
                            conferma all'indirizzo: <em><?php print($customer->email) ?></em></p>
                    <?php } else { ?>
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