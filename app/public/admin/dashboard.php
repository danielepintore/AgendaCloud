<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
$config = Config::getConfig();
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $db = new Database();
    
    $user = new User($db);
    // check if user still exist in the database and is in active status and is in active status
    if (!$user->exist() || !$user->isActive()){
        header("HTTP/1.1 303 See Other");
        header("Location: /admin/logout.php");
    }
} else {
    // user isn't logged
    // redirect to login page
    header("HTTP/1.1 303 See Other");
    header("Location: /admin/index.php");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Dashboard - ".$config->company->name." - AgendaCloud");?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/adminCalendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="../css/loading.min.css"/>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/admin/dashboard.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, DASHBOARD);?>
    <div class="row">
        <!--Calendar-->
        <div class="col-sm-12 col-md-7 col-lg-6 col-xl-5 col-xxl-5 mt-2 mb-2">
            <div id="bookings-calendar" class="calendar-col">
                <div class="calendar-header">
                    <i class="icon-chevron fa-solid fa-chevron-left"></i>
                    <h1></h1>
                    <i class="icon-chevron fa-solid fa-chevron-right"></i>
                </div>
                <div class="calendar-weekdays"></div>
                <div class="calendar-content"></div>
            </div>
        </div>
        <!-- Clients list -->
        <div class="col-sm-12 col-md-5 col-lg-6 col-xl-7 col-xxl-7 mt-2 mb-2">
            <div class="card appuntamentiCard">
                <div class="card-header">
                    Lista appuntamenti:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="appointmentList">
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-12 mt-2 mb-2">
            <div class="card confermaCard w-100">
                <div class="card-header">
                    Appuntamenti da accettare
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="pendingAppointmentsList"></div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sei sicuro?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body">
                <p>Vuoi cancellare la prenotazione?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" id="deleteAppointmentBtn" data-bs-dismiss="modal">Si</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
