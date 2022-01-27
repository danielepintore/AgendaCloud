<?php
include("api/utils.php");
?>
<head>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='css/calendar.css' rel='stylesheet' type='text/css'>
    <link href='https://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' rel='stylesheet' type='text/css'>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
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
              <h5 class="card-title">Scegli un servizio:</h5>
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
        </div>
      </div>
    </div>
  </body>