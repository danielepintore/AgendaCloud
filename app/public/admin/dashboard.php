<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User($_SESSION['logged'], $_SESSION['username'], $_SESSION['password'], $_SESSION['isAdmin']);
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
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/dashboard.js"></script>
</head>
<body>
<div class="container">
    <nav class="navbar navbar-expand-md navbar-light bg-white mb-2">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">AgendaCloud</a>
            <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#rightMenu" aria-controls="rightMenu" aria-expanded="false"
                    aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-0">
                    <li class="nav-item me-2">
                        <a class="nav-link active" aria-current="page" href="#">Calendario</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#">Servizi</a>
                    </li>
                    <li class="nav-item me-2">
                        <a class="nav-link" href="#">Dipendenti</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="collapse navbar-collapse" id="rightMenu">
        <ul class="mobile-nav me-auto mb-0 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="#">Calendario</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="#">Servizi</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="#">Dipendenti</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <!--Calendar-->
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-4 col-xxl-4 mt-2">
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
        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-8">
            <div class="list-group me-4 ms-4 mt-4" id="listaAppuntamenti">
                <a href="#" class="list-group-item list-group-item-action flex-column align-items-start active">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">Nome servizio: Nome Cliente</h5>
                        <small>12:00</small>
                    </div>
                    <p class="mb-1">Note aggiuntive</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">List group item heading</h5>
                        <small class="text-muted">3 days ago</small>
                    </div>
                    <p class="mb-1">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                    <small class="text-muted">Donec id elit non mi porta.</small>
                </a>
                <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">List group item heading</h5>
                        <small class="text-muted">3 days ago</small>
                    </div>
                    <p class="mb-1">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                    <small class="text-muted">Donec id elit non mi porta.</small>
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
