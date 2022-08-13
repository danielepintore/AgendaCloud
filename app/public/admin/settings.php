<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
session_start();
if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['logged']) && $_SESSION['logged'] && $_SESSION['isAdmin']) {
    // user is logged
    // create user object
    $db = new Database();
    $user = new User($db);
    // check if user still exist in the database and is in active status
    if (!$user->exist() || !$user->isActive()) {
        header("HTTP/1.1 303 See Other");
        header("Location: /admin/logout.php");
    }
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
    die(0);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Impostazioni - " . $config->company->name . " - AgendaCloud"); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/admin/settings.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="../js/admin/settings.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, SETTINGS); ?>
    <div class="container">
        <div class="alert alert-danger d-none" id="errorAlert" role="alert">
        </div>
        <div class="col-12 mt-3 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Metodi di pagamento:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="paymentMethodsList">
                    <label class="switch" style="font-size: .75rem">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
