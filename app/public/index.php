<?php
require_once realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php';
?>
<html lang="it">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='css/fontawesome.css' rel='stylesheet' type='text/css'>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.validate.min.js"></script>
    <script src="js/additional-methods.min.js"></script>
    <script type="text/javascript" src="js/calendar.js"></script>
    <script type="text/javascript" src="js/appointments.js"></script>
</head>
<body>
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
                        <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <h5 class="card-title">Scegli un servizio:</h5>
                            <select id="tipoServizio" class="form-select mb-2" aria-label="Default select example">
                                <option value="-1" selected disabled hidden
                                ">Seleziona un servizio</option>
                                <?php
                                $services = Services::getAllServices();
                                if (!$services["error"]) {
                                    // se non è presente un errore
                                    foreach ($services["response"] as $s) {
                                        print('<option value="' . $s["id"] . '">' . $s["Nome"] . '</option>');
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div id="scelta_metodo_pagamento"
                             class="blur active no-click col-sm-12 col-md-6 col-lg-6 col-xl-6">
                            <h5 class="card-title">Scegli un metodo di pagamento:</h5>
                            <select id="tipoPagamento" class="form-select" aria-label="Default select example">
                                <option value="-1" selected disabled hidden
                                ">Seleziona un metodo di pagamento</option>
                                <?php
                                $paymentMethods = Payment::getPaymentMethods();
                                if (!$paymentMethods["error"]) {
                                    // se non è presente un errore
                                    foreach ($paymentMethods["response"] as $paymentMethod) {
                                        print('<option value="' . $paymentMethod["id"] . '">' . $paymentMethod["name"] . '</option>');
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div id="scelta_dipendente" class="blur active no-click">
                        <h5 class="card-title mt-2">Scegli un dipendente:</h5>
                        <select id="lista_dipendenti" class="form-select" disabled="true">
                        </select>
                    </div>
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
            <div id="calendar" class="blur active no-click">
                <div id="calendar_header">
                    <i class="icon-chevron fa-solid fa-chevron-left"></i>
                    <h1></h1>
                    <i class="icon-chevron fa-solid fa-chevron-right"></i>
                </div>
                <div id="calendar_weekdays"></div>
                <div id="calendar_content"></div>
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
                    <select id="lista-orari" class="form-select" aria-label="Default select example">
                    </select>
                </div>
            </div>
            <!--Card i tuoi dati-->
            <div class="card blur active no-click mt-4" id="dati_personali">
                <div class="card-header">
                    I tuoi dati:
                </div>
                <div class="card-body">
                    <form id="form_dati_personali">
                        <div class="row">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-2">
                                <input type="text" class="form-control" id="nomeInput" name="nomeInput"
                                       placeholder="Il tuo nome">
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <input type="text" class="form-control" id="cognomeInput" name="cognomeInput"
                                       placeholder="Il tuo cognome">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-2">
                                <input type="email" class="form-control" id="emailInput" name="emailInput"
                                       placeholder="La tua email">
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <input type="tel" class="form-control" id="phoneInput" name="phoneInput"
                                       placeholder="Il tuo numero telefonico">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <form action="/payment/checkout.php" method="post" id="paymentForm">
                <input type="hidden" id="dayPOST" name="date">
                <input type="hidden" id="idServicePOST" name="serviceId">
                <input type="hidden" id="employeeIdPOST" name="employeeId">
                <input type="hidden" id="slotPOST" name="slot">
                <input type="hidden" id="clientNomePOST" name="clientNome">
                <input type="hidden" id="clientCognomePOST" name="clientCognome">
                <input type="hidden" id="clientEmailPOST" name="clientEmail">
                <input type="hidden" id="clientPhonePOST" name="clientPhone">
                <input type="hidden" id="paymentMethodPOST" name="paymentMethod">
                <input type="button" id="prenota_btn" class="btn btn-success mt-4 mb-4 w-100 blur active no-click"
                       value="Prenota" disabled>
            </form>
        </div>
    </div>
</div>
</body>
</html>