<?php

/**
 * This classes contains all the exceptions related to the services
 */
class ServiceException extends Exception {

    public static function failedToGetServiceData() {
        return new static("Failed to get the service data");
    }
    public static function serviceNotAvailable() {
        return new static("The selected service is disabled or doesn't exist");
    }
}