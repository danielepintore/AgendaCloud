<?php
include("api/utils.php");
?>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' rel='stylesheet' type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/additional-methods.min.js"></script>
    <script type="text/javascript" src="js/calendar.js"></script>
    <script type="text/javascript" src="js/appointments.js"></script>
</head>
  <body>
    <div class="container h-100">
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
                            <option value="-1" selected disabled hidden">Seleziona un servizio</option>
                            <?php
                            $services = get_services();
                            if (!$services["error"]) {
                                // se non è presente un errore
                                foreach ($services["response"] as $s){
                                    if ($s["Durata"] >= 60 ){
                                        if ($s["Durata"] % 60 != 0){
                                            $ore = (int)($s["Durata"] / 60);
                                            $minuti = $s["Durata"] - (60 * $ore);
                                            $time = "$ore ore e $minuti minuti";
                                        } else if ($s["Durata"] / 60 > 1){
                                            $time = ($s["Durata"] / 60)." ore";
                                        } else {
                                            $time = ($s["Durata"] / 60)." ora";
                                        }
                                    } else {
                                        $time = $s["Durata"]." minuti";
                                    }
                                    print('<option value="'.$s["id"].'">'.$s["Nome"].' ['.$time.']</option>');
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6">
                        <h5 class="card-title">Scegli un metodo di pagamento:</h5>
                        <select id="tipoServizio" class="form-select" aria-label="Default select example">
                            <option value="-1" selected disabled hidden">Seleziona un servizio</option>
                            <?php
                            $services = get_services();
                            if (!$services["error"]) {
                                // se non è presente un errore
                                foreach ($services["response"] as $s){
                                    if ($s["Durata"] >= 60 ){
                                        if ($s["Durata"] % 60 != 0){
                                            $ore = (int)($s["Durata"] / 60);
                                            $minuti = $s["Durata"] - (60 * $ore);
                                            $time = "$ore ore e $minuti minuti";
                                        } else if ($s["Durata"] / 60 > 1){
                                            $time = ($s["Durata"] / 60)." ore";
                                        } else {
                                            $time = ($s["Durata"] / 60)." ora";
                                        }
                                    } else {
                                        $time = $s["Durata"]." minuti";
                                    }
                                    print('<option value="'.$s["id"].'">'.$s["Nome"].' ['.$time.']</option>');
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
            </div>
          </div>
        </div>
        <!--Calendar-->
        <div class="col-auto calendar-col mt-4">
          <div id="calendar" class="blur active no-click">
            <div id="calendar_header">
                <i class="icon-chevron-left"></i>
                <h1></h1>
                <i class="icon-chevron-right"></i>
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
              <select id="lista-orari"class="form-select" aria-label="Default select example">
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
                                <input type="text" class="form-control" id="nomeInput" name="nomeInput" placeholder="Il tuo nome">
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <input type="text" class="form-control" id="cognomeInput" name="cognomeInput" placeholder="Il tuo cognome">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 mb-2">
                                <input type="email" class="form-control" id="emailInput" name="emailInput" placeholder="La tua email">
                            </div>
                            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6">
                                <input type="tel" class="form-control" id="phoneInput" name="phoneInput" placeholder="Il tuo numero telefonico">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <form action="/payments/checkout.php" method="post" id="paymentForm">
                <input type="hidden" id="dayPOST" name="date">
                <input type="hidden" id="idServicePOST" name="serviceId">
                <input type="hidden" id="idWorkerPOST" name="workerId">
                <input type="hidden" id="slotPOST" name="slot">
                <input type="hidden" id="clientNomePOST" name="clientNome">
                <input type="hidden" id="clientCognomePOST" name="clientCognome">
                <input type="hidden" id="clientEmailPOST" name="clientEmail">
                <input type="hidden" id="clientPhonePOST" name="clientPhone">
                <input type="button" id="prenota_btn" class="btn btn-success mt-4 mb-4 w-100 blur active no-click" value="Prenota" disabled>
            </form>
        </div>
      </div>
    </div>
    <!-- Modal for confirming output -->
    <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEsito">Prenotazione</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="modalBodyResult" class="modal-body">
                    <p id="modalBodyResultParagraph"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Ok</button>
                </div>
            </div>
        </div>
    </div>
  </body>