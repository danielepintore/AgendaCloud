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
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php print("Servizi - " . $config->company->name . " - AgendaCloud"); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/admin/services.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <link href='../css/loading.min.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script type="text/javascript" src="../js/buttonLoader.js"></script>
    <script type="text/javascript" src="../js/admin/services.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, SERVICES); ?>
    <div class="container">
        <button type="submit" id="addServiceBtn" class="btn btn-success d-block me-0 ms-auto mt-2"><i
                    class="fa-solid fa-plus"></i> Aggiungi un servizio
        </button>
        <div class="col-12 mt-2 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Lista servizi:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="servicesList">
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un nuovo servizio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="addServiceForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="service-name" name="name"
                                   data-error="#errorName">
                        </div>
                        <p id="errorName"></p>
                        <div class="input-group mb-2">
                            <input type="number" placeholder="Durata" class="form-control" id="service-duration"
                                   name="duration" data-error="#errorDuration">
                            <span class="input-group-text">minuti</span>
                        </div>
                        <p id="errorDuration"></p>
                        <div class="mb-2">
                            <label for="service-startTime" class="form-label">Orario apertura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="08:00" class="form-control" id="service-startTime"
                                       name="startTime" data-error="#errorStartTime">
                            </div>
                            <p id="errorStartTime"></p>
                            <label for="service-endTime" class="form-label">Orario chiusura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="12:00" class="form-control" id="service-endTime"
                                       name="endTime" data-error="#errorEndTime">
                            </div>
                            <p id="errorEndTime"></p>
                            <div class="input-group mb-2">
                                <input type="number" value="15" placeholder="Costo" class="form-control"
                                       id="service-cost" name="cost" data-error="#errorCost">
                                <span class="input-group-text">€</span>
                            </div>
                            <p id="errorCost"></p>
                            <label for="service-waitTime" class="form-label">Tempo di attesa tra appuntamenti:</label>
                            <div class="input-group mb-2">
                                <input type="number" value="0" class="form-control" id="service-waitTime"
                                       name="waitTime" data-error="#errorWaitTime">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <p id="errorWaitTime"></p>
                            <label for="service-bookableUntilTime" class="form-label">Non permettere di prenotare se
                                mancano meno di </label>
                            <div class="input-group mb-2">
                                <input type="number" value="0" class="form-control" id="service-bookableUntilTime"
                                       name="bookableUntil" data-error="#errorBookableUntil">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <p id="errorBookableUntil"></p>
                        </div>
                        <div class="mb-2">
                            <label for="service-description" class="col-form-label">Descrizione servizio:</label>
                            <textarea class="form-control" id="service-description"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="service-active" checked>
                            <label class="form-check-label" for="service-active">
                                Servizio attivo istantaneamente
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddServiceBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Aggiungi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica un servizio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="editServiceForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="service-name-edit"
                                   name="name" data-error="#errorName-edit">
                        </div>
                        <p id="errorName-edit"></p>
                        <div class="input-group mb-2">
                            <input type="number" placeholder="Durata" class="form-control" id="service-duration-edit"
                                   name="duration" data-error="#errorDuration-edit">
                            <span class="input-group-text">minuti</span>
                        </div>
                        <p id="errorDuration-edit"></p>
                        <div class="mb-2">
                            <label for="service-startTime-edit" class="form-label">Orario apertura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="service-startTime-edit"
                                       name="startTime" data-error="#errorStartTime-edit">
                            </div>
                            <p id="errorStartTime-edit"></p>
                            <label for="service-endTime-edit" class="form-label">Orario chiusura:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="service-endTime-edit"
                                       name="endTime" data-error="#errorEndTime-edit">
                            </div>
                            <p id="errorEndTime-edit"></p>
                            <div class="input-group mb-2">
                                <input type="number" value="" placeholder="Costo" class="form-control"
                                       id="service-cost-edit" name="cost" data-error="#errorCost-edit">
                                <span class="input-group-text">€</span>
                            </div>
                            <p id="errorCost-edit"></p>
                            <label for="service-waitTime-edit" class="form-label">Tempo di attesa tra
                                appuntamenti:</label>
                            <div class="input-group mb-2">
                                <input type="number" value="" class="form-control" id="service-waitTime-edit"
                                       name="waitTime" data-error="#errorWaitTime-edit">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <p id="errorWaitTime-edit"></p>
                            <label for="service-bookableUntilTime-edit" class="form-label">Non permettere di prenotare
                                se mancano meno di </label>
                            <div class="input-group mb-2">
                                <input type="number" value="" class="form-control" id="service-bookableUntilTime-edit"
                                       name="bookableUntil" data-error="#errorBookableUntil-edit">
                                <span class="input-group-text">minuti</span>
                            </div>
                            <p id="errorBookableUntil-edit"></p>
                        </div>
                        <div class="mb-2">
                            <label for="service-description-edit" class="col-form-label">Descrizione servizio:</label>
                            <textarea class="form-control" id="service-description-edit" name="description"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="service-active-edit" checked>
                            <label class="form-check-label" for="service-active-edit">
                                Servizio attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="editServiceBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Modifica
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="showEmployeesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lista dipendenti:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="employeesInfo"></p>
                    <table class="table" id="employeesTable">
                        <thead>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        </thead>
                        <tbody id="employeesTableContent">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Chiudi
                    </button>
                    <button type="button" class="btn btn-success" id="editEmployeesBtn" data-bs-dismiss="modal"><i
                                class="fa-solid fa-pen"></i> Modifica i dipendenti
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmployeesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi dei dipendenti:</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="employeesToAddInfo"></p>
                    <div class="input-group mb-2">
                        <input type="text" placeholder="Nome" class="form-control" id="employeeNameSearch">
                    </div>
                    <table class="table d-none text-center" id="editEmployeesTable">
                        <thead>
                        <th scope="col">Nome</th>
                        <th scope="col">Cognome</th>
                        <th scope="col">Azione</th>
                        </thead>
                        <tbody id="editEmployeesTableContent">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn" data-bs-dismiss="modal">
                        Chiudi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vuoi eliminare questo servizio?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteServiceBtn" data-bs-dismiss="modal">
                        Elimina
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewHolidaysModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi dei giorni di chiusura per il servizio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="searchDateForm">
                        <div class="input-group mb-2">
                            <input type="date" placeholder="Giorno" max="2099-12-31" class="form-control"
                                   id="daySearchHoliday" data-error="#infoHolidayService">
                        </div>
                    </form>
                    <p id="infoHolidayService""></p>
                    <table class="table d-none text-center" id="serviceHolidayTable">
                        <thead>
                        <th scope="col">Giorno</th>
                        <th scope="col">Ora inizio</th>
                        <th scope="col">Ora fine</th>
                        <th scope="col">Azione</th>
                        </thead>
                        <tbody id="serviceHolidayTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-success" id="addHolidayButton" data-bs-dismiss="modal"><i
                                class="fa-solid fa-plus"></i> Aggiungi un giorno
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addHolidayModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi una nuova giornata</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="addHolidayForm">
                        <div class="mb-2">
                            <label for="holidayDate" class="form-label">Data:</label>
                            <input type="date" placeholder="Data" class="form-control" id="holidayDate"
                                   name="holidayDate"
                                   data-error="#errorHolidayDate">
                        </div>
                        <p id="errorHolidayDate"></p>
                        <div class="mb-2">
                            <label for="holidayStartTime" class="form-label">Orario inizio:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="08:00" class="form-control" id="holidayStartTime"
                                       name="holidayStartTime" data-error="#errorholidayStartTime">
                            </div>
                            <p id="errorholidayStartTime"></p>
                            <label for="holidayEndTime" class="form-label">Orario fine:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="12:00" class="form-control" id="holidayEndTime"
                                       name="holidayEndTime" data-error="#errorholidayEndTime">
                            </div>
                            <p id="errorholidayEndTime"></p>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="holidayFullDayCheckBox">
                            <label class="form-check-label" for="service-active">
                                Tutto il giorno
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddHolidayButton"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Aggiungi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="successModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="errorModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="errorModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
