<?php

class Config {
    public static function getConfig() {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $dotenv = Dotenv\Dotenv::createImmutable(realpath(dirname(__FILE__, 3)));
        $dotenv->load();
        $config = (object)array(
            "db" => (object)array(
                "dbname" => $_ENV['DB_NAME'],
                "username" => $_ENV['DB_USER'],
                "password" => $_ENV['DB_PWD'],
                "host" => $_ENV['DB_HOST'],
                "expire_user" => $_ENV['DB_EXPIRE_USER'],
                "expire_pwd" => $_ENV['DB_EXPIRE_PWD'],
                "email_user" => $_ENV['DB_EMAIL_USER'],
                "email_pwd" => $_ENV['DB_EMAIL_PWD'],
            ),
            /*
             * The environment array can be like this:
             * name variable can be: production, debug
             * debug value can be true or false
             */
            "company" => (object)array(
                "name" => $_ENV["COMPANY_NAME"],
            ),
            "mail" => (object)array(
                "sender" => $_ENV["SENDER_EMAIL"],
                "username" => $_ENV["MAIL_USERNAME"],
                "password" => $_ENV["MAIL_PASSWORD"],
                "hostname" => $_ENV["MAIL_HOSTNAME"],
                "port" => $_ENV["MAIL_PORT"],
                "supervisor" => $_ENV["SUPERVISOR_MAIL"],
                "company" => $_ENV["COMPANY_MAIL"],
            ),
            "environment" => (object)array(
                "name" => "debug", // TODO: change to production and disable debug
                "debug" => true,
            ),
            "urls" => (object)array(
                "baseUrl" => "http://" . $_ENV['DOMAIN_NAME'] // TODO change to https in production
            ),
            "stripe" => (object)array(
                "secret_api_key" => $_ENV['STRIPE_SECRET_API_KEY'],
                "endpoint_secret" => $_ENV['STRIPE_ENDPOINT_SECRET'],
                "session_timeout" => 5,
            ),
            "calendar" => (object)array(
                "max_future_day" => $_ENV['MAX_FUTURE_DAY_CALENDAR'],
            ),
        );
        return $config;
    }
}