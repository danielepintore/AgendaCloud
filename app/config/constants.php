<?php
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

defined("CREDIT_CARD")
or define("CREDIT_CARD", 1);
defined("CASH")
or define("CASH", 2);

defined("DEBUG")
or define("DEBUG", true);