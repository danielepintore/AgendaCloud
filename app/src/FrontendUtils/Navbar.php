<?php

class Navbar {

    /*
     * Example of dropdown
     * <div class="dropdown">
     * <div class="nav-item nav-link me-2 dropdown-toggle" role="button"
     * id="dropdownMenuAppuntamenti" data-bs-toggle="dropdown" aria-expanded="false">
     * Appuntamenti
     * </div>
     * <ul class="dropdown-menu" aria-labelledby="dropdownMenuAppuntamenti">
     * <li><a class="dropdown-item" href="add_appointment.php">Nuovo appuntamento</a>
     * </li>
     * <li><a class="dropdown-item" href="appointment_editor.php">Gestisci
     * appuntamenti</a></li>
     * </ul>
     * </div>
     */

    public static function printNavBar($user, $selectedItem, $isDropDown = false, $dropDownIndex = 0) { ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-white mb-2">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">AgendaCloud</a>
                <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#rightMenu" aria-controls="rightMenu" aria-expanded="false"
                        aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-0">
                        <?php switch ($selectedItem) {
                            case DASHBOARD:
                                ?>
                                <li class="nav-item me-2">
                                    <a class="nav-link active" aria-current="page" href="dashboard.php">Calendario</a>
                                </li>
                                <li class="nav-item me-2">
                                    <a class="nav-link" aria-current="page" href="add_appointment.php">Nuovo
                                        appuntamento</a>
                                </li>
                                <?php break;
                            case APPOINTMENT:
                                ?>
                                <li class="nav-item me-2">
                                    <a class="nav-link" aria-current="page" href="dashboard.php">Calendario</a>
                                </li>
                                <li class="nav-item me-2">
                                    <a class="nav-link active" aria-current="page" href="add_appointment.php">Nuovo
                                        appuntamento</a>
                                </li>
                                <?php break;
                            default: ?>
                                <li class="nav-item me-2">
                                    <a class="nav-link" aria-current="page" href="dashboard.php">Calendario</a>
                                </li>
                                <li class="nav-item me-2">
                                    <a class="nav-link" aria-current="page" href="add_appointment.php">Nuovo
                                        appuntamento</a>
                                </li>
                                <?php break;
                        } ?>
                        <?php if ($user->isLogged() && $user->isAdmin()) {
                            switch ($selectedItem) {
                                case SERVICES:
                                    ?>
                                    <li class="nav-item me-2">
                                        <a class="nav-link active" href="services.php">Servizi</a>
                                    </li>
                                    <li class="nav-item me-2">
                                        <a class="nav-link" href="employees.php"">Dipendenti</a>
                                    </li>
                                    <?php break;
                                case EMPLOYEES:
                                    ?>
                                    <li class="nav-item me-2">
                                        <a class="nav-link" href="services.php">Servizi</a>
                                    </li>
                                    <li class="nav-item me-2">
                                        <a class="nav-link active" href="employees.php"">Dipendenti</a>
                                    </li>
                                    <?php break;
                                default: ?>
                                    <li class="nav-item me-2">
                                        <a class="nav-link" href="services.php">Servizi</a>
                                    </li>
                                    <li class="nav-item me-2">
                                        <a class="nav-link" href="employees.php"">Dipendenti</a>
                                    </li>
                                    <?php break;
                            }
                        } ?>
                    </ul>
                    <ul class="navbar-nav ms-auto mb-0">
                        <li class="nav-item me-2">
                            <a class="nav-link" aria-current="page" href="logout.php"><i
                                        class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="collapse navbar-collapse" id="rightMenu">
            <ul class="mobile-nav me-auto mb-0 mb-lg-0">
                <?php switch ($selectedItem) {
                    case DASHBOARD:
                        ?>
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="dashboard.php">Calendario</a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link" href="add_appointment.php">Nuovo appuntamento</a>
                        </li>
                        <?php break;
                    case APPOINTMENT: ?>
                        <li class="nav-item">
                            <a class="nav-link " aria-current="page" href="dashboard.php">Calendario</a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link active" href="add_appointment.php">Nuovo appuntamento</a>
                        </li>
                        <?php break;
                    default: ?>
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="dashboard.php">Calendario</a>
                        </li>
                        <li class="nav-item mt-2">
                            <a class="nav-link" href="add_appointment.php">Nuovo appuntamento</a>
                        </li>
                        <?php break;
                } ?>
                <?php if ($user->isLogged() && $user->isAdmin()) {
                    switch ($selectedItem) {
                        case SERVICES: ?>
                            <li class="nav-item mt-2">
                                <a class="nav-link active" href="services.php">Servizi</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link" href="employees.php">Dipendenti</a>
                            </li>
                            <?php break;
                        case EMPLOYEES:
                            ?>
                            <li class="nav-item mt-2">
                                <a class="nav-link" href="services.php">Servizi</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link active" href="employees.php">Dipendenti</a>
                            </li>
                            <?php break;
                        default:
                            ?>
                            <li class="nav-item mt-2">
                                <a class="nav-link" href="services.php">Servizi</a>
                            </li>
                            <li class="nav-item mt-2">
                                <a class="nav-link" href="employees.php">Dipendenti</a>
                            </li>
                            <?php break;
                    }
                } ?>
                <li class="nav-item mt-2">
                    <a class="nav-link" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Logout</a>
                </li>
            </ul>
        </div>
        <?php
    }
}