<?php

/**
 * This classes contains all the exceptions related to the employees
 */
class EmployeeException extends Exception {

    public static function employeeIsNotActive() {
        return new static("The employee selected isn't active");
    }
}