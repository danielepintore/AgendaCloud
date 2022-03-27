<?php

use Admin\User;

require_once realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php';
session_start();
if (session_status() == PHP_SESSION_ACTIVE && $_SESSION['logged']) {
    // user is logged
    // create user object
    $user = new User();
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
    <link href='../css/adminCalendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/admin/dashboard.js"></script>
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
                    <div class="dropdown">
                        <div class="nav-item nav-link me-2 dropdown-toggle" role="button" id="dropdownMenuAppuntamenti" data-bs-toggle="dropdown" aria-expanded="false">
                            Appuntamenti
                        </div>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuAppuntamenti">
                            <li><a class="dropdown-item" href="appuntamenti.php">Nuovo appuntamento</a></li>
                            <li><a class="dropdown-item" href="#">Gestisci appuntamenti</a></li>
                        </ul>
                    </div>
                    <?php if ($user->isLogged() && $user->isAdmin()){ ?>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="servizi.php">Servizi</a>
                        </li>
                        <li class="nav-item me-2">
                            <a class="nav-link" href="dipendenti.php"">Dipendenti</a>
                        </li>
                    <?php } ?>
                </ul>
                <ul class="navbar-nav ms-auto mb-0">
                    <li class="nav-item me-2">
                        <a class="nav-link" aria-current="page" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
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
                <a class="nav-link" href="appuntamenti.php">Nuovo appuntamento</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="appuntamenti.php">Gestisci appuntamenti</a>
            </li>
            <?php if ($user->isLogged() && $user->isAdmin()){ ?>
            <li class="nav-item mt-2">
                <a class="nav-link" href="servizi.php">Servizi</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link" href="dipendenti.php">Dipendenti</a>
            </li>
            <?php } ?>
            <li class="nav-item mt-2">
                <a class="nav-link" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
            </li>
        </ul>
    </div>
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
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="pendingAppointmentsList">

                </div>
            </div>
        </div>
    </div>
</div>
</div>
</body>
</html>
