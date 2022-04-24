<?php
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

defined("DEBUG")
or define("DEBUG", true); // todo change in production