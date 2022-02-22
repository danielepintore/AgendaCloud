<?php

class ServiceException extends Exception {
    public static function failedToGetServiceData() {
        return new static("Failed to get the service data");
    }
}