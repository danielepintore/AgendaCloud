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
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='../css/admin/appointment.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/calendar.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script type="text/javascript" src="../js/admin/appointments.js"></script>
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
                        <a class="nav-link" aria-current="page" href="dashboard.php">Calendario</a>
                    </li>
                    <div class="dropdown">
                        <div class="nav-item nav-link active me-2 dropdown-toggle" role="button" id="dropdownMenuAppuntamenti" data-bs-toggle="dropdown" aria-expanded="false">
                            Appuntamenti
                        </div>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuAppuntamenti">
                            <li><a class="dropdown-item active no-click" href="appuntamenti.php">Nuovo appuntamento</a></li>
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
                <a class="nav-link" aria-current="page" href="dashboard.php">Calendario</a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link active" href="#">Appuntamenti</a>
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
    <div class="container">
        <div class="row">
            <!--Servizi-->
            <div class="col-12 col-md-12 mt-4">
                <!--Card servizi-->
                <div class="card">
                    <div class="card-header">
                        Servizi disponibili:
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                <h5 class="card-title">Scegli un servizio:</h5>
                                <select id="tipoServizio" class="form-select mb-2" aria-label="Default select example">
                                    <option value="-1" selected disabled hidden
                                    ">Seleziona un servizio</option>
                                    <?php
                                    try {
                                        $services = \Admin\Services::getEmployeeService($user->IsAdmin(), $user->getId());
                                        // se non è presente un errore
                                        foreach ($services as $s) {
                                            print('<option value="' . $s["id"] . '">' . $s["Nome"] . '</option>');
                                        }
                                    } catch (DatabaseException | Exception $e){
                                        header("HTTP/1.1 303 See Other");
                                        header("Location: /error.php");
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <?php if ($user->IsAdmin()){ ?>
                            <div id="scelta_dipendente" class="blur active no-click">
                                <h5 class="card-title mt-2">Scegli un dipendente:</h5>
                                <select id="lista_dipendenti" class="form-select" disabled="true">
                                </select>
                            </div>
                        <?php } ?>
                        <div id="info-servizio" class="mt-2 blur active no-click">
                            <span>Durata servizio: </span>
                            <span id="time-lenght"></span>
                            <span>costo servizio: </span>
                            <span id="prezzo-servizio"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!--Calendar-->
            <div class="col-auto calendar-col mt-4">
                <div id="bookings-calendar" class="calendar blur active no-click">
                    <div class="calendar-header">
                        <i class="icon-chevron fa-solid fa-chevron-left"></i>
                        <h1></h1>
                        <i class="icon-chevron fa-solid fa-chevron-right"></i>
                    </div>
                    <div class="calendar-weekdays"></div>
                    <div class="calendar-content"></div>
                </div>
            </div>
            <!--Orari-->
            <div class="col-12 col-md mt-4">
                <!--Card servizi-->
                <div class="card blur active no-click" id="orari">
                    <div class="card-header">
                        Orari disponibili:
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Scegli un orario da qua sotto:</h5>
                        <select id="lista-orari" class="form-select">
                        </select>
                    </div>
                </div>
                <!--Card i tuoi dati-->
                <div class="card blur active no-click mt-4" id="dati_personali">
                    <div class="card-header">
                        I dati del cliente:
                    </div>
                    <div class="card-body">
                        <form id="form_dati_personali">
                            <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-2">
                                    <input type="text" class="form-control" id="nomeInput" name="nomeInput"
                                           placeholder="Nome">
                                </div>
                                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                    <input type="text" class="form-control" id="cognomeInput" name="cognomeInput"
                                           placeholder="Cognome">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-2">
                                    <input type="email" class="form-control" id="emailInput" name="emailInput"
                                           placeholder="Email">
                                </div>
                                <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                    <input type="tel" class="form-control" id="phoneInput" name="phoneInput"
                                           placeholder="Cellulare">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <form action="api/book.php" method="post" id="paymentForm">
                    <input type="hidden" id="dayPOST" name="date">
                    <input type="hidden" id="idServicePOST" name="serviceId">
                    <input type="hidden" id="employeeIdPOST" name="employeeId">
                    <input type="hidden" id="slotPOST" name="slot">
                    <input type="hidden" id="clientNomePOST" name="clientNome">
                    <input type="hidden" id="clientCognomePOST" name="clientCognome">
                    <input type="hidden" id="clientEmailPOST" name="clientEmail">
                    <input type="hidden" id="clientPhonePOST" name="clientPhone">
                    <input type="button" id="prenota_btn" class="btn btn-success mt-4 mb-4 w-100 blur active no-click"
                           value="Prenota" disabled>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
