<?php
require_once(realpath(dirname(__FILE__, 1)) . '/config.php');
/*
    Error reporting
*/
if ($config["environment"]["debug"]) {
    // set error reporting to all
    error_reporting(E_ALL);
} else {
    // otherwise, disable error reporting
    error_reporting(0);
}
