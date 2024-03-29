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
    <link href='../css/dataTables.bootstrap5.min.css' rel='stylesheet'>
    <link href='../css/dataTablesStyle.css' rel='stylesheet'>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script src="../js/jquery.dataTables.min.js"></script>
    <script src="../js/dataTables.bootstrap5.min.js"></script>
    <script src="../js/buttonLoader.js"></script>
    <script src="../js/admin/services.js"></script>
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
                                <input type="time" value="18:00" class="form-control" id="service-endTime"
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

    <div class="modal fade" id="workingTimesServiceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imposta gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body" style="">
                    <div id="serviceWorkTimesTab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#defaultServiceWorkTimes"
                                        type="button" role="tab" aria-controls="Orari standard" aria-selected="true">
                                    Orari standard
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#customServiceWorkTimes" type="button" role="tab"
                                        aria-controls="Orari speciali" aria-selected="false">Orari speciali
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="defaultServiceWorkTimes" role="tabpanel"
                                 aria-labelledby="defaultServiceWorkTimes">
                                <div id="defaultServiceWorkTimesTable" class="mb-2"></div>
                                <button id="showModalEditServiceWorkingTimeBtn" type="button"
                                        class="btn btn-success w-100"><i
                                            class="fa-solid fa-pen"></i> Modifica orari
                                </button>
                            </div>
                            <div class="tab-pane fade" id="customServiceWorkTimes" role="tabpanel"
                                 aria-labelledby="customServiceWorkTimes">
                                <div id="customServiceWorkTimesTable" class="mb-2 mt-2"></div>
                                <button id="showModalCustomAddServiceWorkingTimeBtn" type="button"
                                        class="btn btn-success w-100 mt-2"><i
                                            class="fa-solid fa-plus"></i> Aggiungi orari
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <h6>Scegli un giorno:</h6>
                    <div class="day-container">
                        <div class="day-selector first-day" value="1">Lun</div>
                        <div class="day-selector" value="2">Mar</div>
                        <div class="day-selector" value="3">Mer</div>
                        <div class="day-selector" value="4">Gio</div>
                        <div class="day-selector" value="5">Ven</div>
                        <div class="day-selector" value="6">Sab</div>
                        <div class="day-selector last-day" value="7">Dom</div>
                    </div>
                    <form id="updateServiceWorkTimeForm">
                        <h6>Orario inizio lavoro:</h6>
                        <input type="time" value="08:00" class="form-control mb-2" id="workTime-serviceStartTime"
                               name="serviceStartTime">
                        <h6>Orario fine lavoro:</h6>
                        <input type="time" value="17:00" class="form-control mb-2" id="workTime-serviceEndTime"
                               name="serviceEndTime">
                        <h6>Orario inizio pausa:</h6>
                        <input type="time" value="13:00" class="form-control mb-2" id="workTime-serviceStartBreak"
                               name="serviceStartBreak">
                        <h6>Orario fine pausa:</h6>
                        <input type="time" value="15:00" class="form-control mb-2" id="workTime-serviceEndBreak"
                               name="serviceEndBreak">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="close-day-checkbox"
                                   name="closeDayCheckbox">
                            <label class="form-check-label">
                                Giorno di chiusura
                            </label>
                        </div>
                    </form>
                    <div class="alert alert-danger d-flex align-items-center mt-2 mb-0 d-none" id="workTimeServiceAlert"
                         role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div>
                            Devi selezionare dei giorni dalla barra qui sopra!
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-success" id="editServiceWorkingTimeButton"><i
                                class="fa-solid fa-pen"></i><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Modifica
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomServiceWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un orario di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomServiceWorkTimeForm">
                        <h6>Data inizio:</h6>
                        <input type="date" class="form-control mb-2" id="workTime-startServiceCustomDay"
                               name="startServiceCustomDay">
                        <h6>Data fine:</h6>
                        <input type="date" class="form-control mb-2" id="workTime-endServiceCustomDay"
                               name="endServiceCustomDay">
                        <h6>Orario inizio lavoro:</h6>
                        <input type="time" value="08:00" class="form-control mb-2" id="workTime-customServiceStartTime"
                               name="serviceCustomStartTime">
                        <h6>Orario fine lavoro:</h6>
                        <input type="time" value="17:00" class="form-control mb-2" id="workTime-customServiceEndTime"
                               name="serviceCustomEndTime">
                        <h6>Orario inizio pausa:</h6>
                        <input type="time" value="13:00" class="form-control mb-2" id="workTime-customServiceStartBreak"
                               name="serviceCustomStartBreak">
                        <h6>Orario fine pausa:</h6>
                        <input type="time" value="15:00" class="form-control mb-2" id="workTime-customServiceEndBreak"
                               name="serviceCustomEndBreak">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="close-day-custom-checkbox"
                                   name="closeDayCustomCheckbox">
                            <label class="form-check-label">
                                Giorno libero
                            </label>
                        </div>
                    </form>
                    <div class="alert alert-danger d-flex align-items-center mt-2 mb-0 d-none"
                         id="customServiceWorkTimeAlert" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        <div>
                            Devi selezionare un giorno che non sia già passato!
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="addCustomServiceWorkingTimeButton"><i
                                class="fa-solid fa-plus"></i><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Aggiungi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="conflictWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="conflictWorkTimesModalTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <p id="conflictWorkTimesModalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-success" id="confirmOvverideServiceWorkTimesBtn">
                        <span class="ld ld-ring ld-cycle loading-circle d-none"></span> Si
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
