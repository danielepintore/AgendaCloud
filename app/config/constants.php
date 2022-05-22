<?php
require_once realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php';
$config = Config::getConfig();
// Payment status constants
defined("PAYMENT_PENDING")
or define("PAYMENT_PENDING", 2);
defined("PAYMENT_EXPIRED")
or define("PAYMENT_EXPIRED", 3);
defined("APPOINTMENT_CONFIRMED")
or define("APPOINTMENT_CONFIRMED", 4);
defined("REJECTED_BY_USER")
or define("REJECTED_BY_USER", 5);
defined("WAITING_APPROVAL")
or define("WAITING_APPROVAL", 6);
defined("CANCELED")
or define("CANCELED", 7);

// payment type constants
defined("CREDIT_CARD")
or define("CREDIT_CARD", 1);
defined("CASH")
or define("CASH", 2);

// User type constants
defined("ADMIN_USER")
or define("ADMIN_USER", 0);
defined("WORKER_USER")
or define("WORKER_USER", 1);

// Pages id constants
defined("DASHBOARD")
or define("DASHBOARD", 1);
defined("APPOINTMENT")
or define("APPOINTMENT", 2);
defined("SERVICES")
or define("SERVICES", 3);
defined("EMPLOYEES")
or define("EMPLOYEES", 4);
defined("SETTINGS")
or define("SETTINGS", 5);

// Constants for weekdays
defined("LUNEDI")
or define("LUNEDI", 0);
defined("MARTEDI")
or define("MARTEDI", 1);
defined("MERCOLEDI")
or define("MERCOLEDI", 2);
defined("GIOVEDI")
or define("GIOVEDI", 3);
defined("VENERDI")
or define("VENERDI", 4);
defined("SABATO")
or define("SABATO", 5);
defined("DOMENICA")
or define("DOMENICA", 6);

defined("DEBUG")
or define("DEBUG", $config->environment->debug);