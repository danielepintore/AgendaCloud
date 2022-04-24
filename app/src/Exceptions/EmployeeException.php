<?php

class EmployeeException extends Exception {
    public static function employeeIsNotActive() {
        return new static("The employee selected isn't active");
    }
}