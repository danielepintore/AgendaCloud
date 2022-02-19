<?php
/*
 * From composer we load the dotEnv module and we fetch the variables that are located in the project .env file
 */

require_once(realpath(dirname(__FILE__, 2)) . '/vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(realpath(dirname(__FILE__, 2)));
$dotenv->load();
$config = array(
    "db" => array(
        "dbname" => $_ENV['DB_NAME'],
        "username" => $_ENV['DB_USER'],
        "password" => $_ENV['DB_PWD'],
        "host" => $_ENV['DB_HOST'],
    ),
    /*
     * The environment array can be like this:
     * name variable can be: production, debug
     * debug value can be true or false
     */
    "environment" => array(
        "name" => "debug", // TODO: change to production and disable debug
        "debug" => true,
    ),
    "urls" => array(
        "baseUrl" => "http://" . $_ENV['DOMAIN_NAME'] // TODO change to https in production
    ),
);

/*
    Creating constants for heavily used paths makes things a lot easier.
    ex. require_once(LIBRARY_PATH . "Paginator.php")
*/
defined("BASE_PATH")
or define("BASE_PATH", realpath(dirname(__FILE__, 2)));

defined("SRC_PATH")
or define("SRC_PATH", BASE_PATH . '/src');

defined("TEMPLATES_PATH")
or define("TEMPLATES_PATH", BASE_PATH . '/templates');

defined("PUBLIC_PATH")
or define("PUBLIC_PATH", BASE_PATH . '/public');

defined("COMPOSER_AUTOLOAD")
or define("COMPOSER_AUTOLOAD", BASE_PATH . '/vendor/autoload.php');

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
?>