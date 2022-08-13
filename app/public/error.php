<?php
require_once realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php';
$config = Config::getConfig();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Errore - " . $config->company->name . " - AgendaCloud"); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='css/status-page.css' rel='stylesheet' type='text/css'>
    <link href='css/fontawesome.css' rel='stylesheet'>
    <script src="js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container-fluid d-flex align-items-center justify-content-center error-container">
    <div class="card">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-triangle-exclamation" id="error-img"></i>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-12 d-flex align-items-center justify-content-center">
                    <p class="card-text">C'è stato un errore ti preghiamo di riprovare</p>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <a href="/">
                        <button type="button" id="home-btn" class="btn btn-secondary">Riprova</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>