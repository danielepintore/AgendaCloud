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
    <title><?php print("Dipendenti - " . $config->company->name . " - AgendaCloud"); ?></title>
    <link rel="apple-touch-icon" sizes="180x180" href="../img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="../img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../img/favicon/favicon-16x16.png">
    <link rel="manifest" href="../img/favicon/site.webmanifest">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='../css/bootstrap.min.css' rel='stylesheet' type='text/css'>
    <link href='../css/dashboard.css' rel='stylesheet' type='text/css'>
    <link href='../css/fontawesome.css' rel='stylesheet'>
    <link href='../css/admin/employees.css' rel='stylesheet'>
    <link rel="stylesheet" type="text/css" href="../css/loading.min.css"/>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.validate.min.js"></script>
    <script src="../js/additional-methods.min.js"></script>
    <script src="../js/buttonLoader.js"></script>
    <script src="../js/admin/employees.js"></script>
</head>
<body>
<div class="container">
    <?php Navbar::printNavBar($user, EMPLOYEES); ?>
    <div class="container">
        <button type="submit" id="addEmployeeBtn" class="btn btn-success d-block me-0 ms-auto mt-2"><i
                    class="fa-solid fa-plus"></i> Aggiungi un dipendente
        </button>
        <div class="col-12 mt-2 mb-2">
            <div class="card w-auto">
                <div class="card-header">
                    Lista dipendenti:
                </div>
                <div class="list-group list-group-flush me-1 ms-1 mt-1 mb-1" id="employeeList">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un nuovo dipendente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="addEmployeeForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name" name="name">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname" name="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role" name="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username"
                                   name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Password" class="form-control" id="password"
                                   name="password">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin">
                            <label class="form-check-label">
                                Amministratore
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="isActive" checked>
                            <label class="form-check-label">
                                Attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmAddEmployeeBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span>
                        Aggiungi
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica un dipendente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="editEmployeeForm">
                        <div class="mb-2">
                            <input type="text" placeholder="Nome" class="form-control" id="name-edit" name="name">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Cognome" class="form-control" id="surname-edit"
                                   name="surname">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Ruolo" class="form-control" id="role-edit" name="role">
                        </div>
                        <div class="mb-2">
                            <input type="text" placeholder="Username" class="form-control" id="username-edit"
                                   name="username">
                        </div>
                        <div class="mb-2">
                            <input type="password" placeholder="Nuova password" class="form-control" id="password-edit"
                                   name="password">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="admin-edit">
                            <label class="form-check-label" for="service-active">
                                Amministratore
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="isActive-edit" checked>
                            <label class="form-check-label">
                                Attivo
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-success" id="confirmEditEmployeeBtn"><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span>
                        Modifica
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Vuoi eliminare questo dipendente?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    Il dipendente selezionato sarà eliminato in modo irreversibile.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteEmployeeBtn" data-bs-dismiss="modal">
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
                    <h5 class="modal-title">Aggiungi dei giorni di ferie per il dipendente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <form id="searchDateForm">
                        <div class="input-group mb-2">
                            <input type="date" placeholder="Giorno" max="2099-12-31" class="form-control"
                                   id="daySearchHoliday" data-error="#infoHolidayUser">
                        </div>
                    </form>
                    <p id="infoHolidayUser""></p>
                    <table class="table d-none text-center" id="userHolidayTable">
                        <thead>
                        <th scope="col">Giorno</th>
                        <th scope="col">Ora inizio</th>
                        <th scope="col">Ora fine</th>
                        <th scope="col">Azione</th>
                        </thead>
                        <tbody id="userHolidayTableBody">
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
                                   data-error="#errorAddHolidayDate">
                        </div>
                        <p id="errorAddHolidayDate"></p>
                        <div class="mb-2">
                            <label for="holidayStartTime" class="form-label">Orario inizio:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="holidayStartTime"
                                       name="holidayStartTime" data-error="#errorAddHolidayStartTime">
                            </div>
                            <p id="errorAddHolidayStartTime"></p>
                            <label for="holidayEndTime" class="form-label">Orario fine:</label>
                            <div class="input-group mb-2">
                                <input type="time" value="" class="form-control" id="holidayEndTime"
                                       name="holidayEndTime" data-error="#errorAddHolidayEndTime">
                            </div>
                            <p id="errorAddHolidayEndTime"></p>
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

    <div class="modal fade" id="workTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Imposta gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body" style="">
                    <div id="employeeWorkTimesTab">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#defaultWorkTimes"
                                        type="button" role="tab" aria-controls="Orari standard" aria-selected="true">
                                    Orari standard
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#customWorkTimes" type="button" role="tab"
                                        aria-controls="Orari speciali" aria-selected="false">Orari speciali
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="defaultWorkTimes" role="tabpanel"
                                 aria-labelledby="defaultWorkTimes">
                                <div id="defaultWorkTimesTable" class="mb-2"></div>
                                <button id="showModalEditWorkingTimeBtn" type="button" class="btn btn-success w-100"><i
                                            class="fa-solid fa-pen"></i> Modifica orari
                                </button>
                            </div>
                            <div class="tab-pane fade" id="customWorkTimes" role="tabpanel"
                                 aria-labelledby="customWorkTimes">
                                <div id="customWorkTimesTable" class="mb-2"></div>
                                <button id="showCustomWorkingTimeModal" type="button" class="btn btn-success w-100"><i
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

    <div class="modal fade" id="editWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modifica gli orari di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <h6>Scegli un giorno:</h6>
                    <div class="day-container">
                        <div class="day-selector first-day">Lun</div>
                        <div class="day-selector">Mar</div>
                        <div class="day-selector">Mer</div>
                        <div class="day-selector">Gio</div>
                        <div class="day-selector">Ven</div>
                        <div class="day-selector">Sab</div>
                        <div class="day-selector last-day">Dom</div>
                    </div>
                    <h6>Orario inizio lavoro:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario fine lavoro:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario inizio pausa:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario fine pausa:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="free-day-checkbox">
                        <label class="form-check-label">
                            Giorno libero
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
                    <button type="button" class="btn btn-success" id="editWorkingTimeButton"><i
                                class="fa-solid fa-pen"></i><span
                                class="ld ld-ring ld-cycle loading-circle d-none"></span> Modifica
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCustomWorkTimesModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Aggiungi un orario di lavoro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                </div>
                <div class="modal-body">
                    <h6>Scegli un giorno:</h6>
                    <input type="date" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario inizio lavoro:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario fine lavoro:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario inizio pausa:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <h6>Orario fine pausa:</h6>
                    <input type="time" value="08:00" class="form-control mb-2" id="workTime-startTime" name="startTime"
                           data-error="#errorStartTime">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="free-day-custom-checkbox">
                        <label class="form-check-label">
                            Giorno libero
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="addCustomWorkingTimeButton"><i
                                class="fa-solid fa-plus"></i><span
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
