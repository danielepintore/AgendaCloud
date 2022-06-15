<?php

/**
 * This classes contains all the exceptions related to the dates
 */
class DateException extends Exception {

    public static function invalidData() {
        return new static('The selected date is invalid');
    }

    public static function wrongIntervalString() {
        return new static('The string passed to the interval is invalid');
    }

    public static function wrongStartOrEndTime() {
        return new static('The string passed to DateTime constructor is invalid');
    }

    public static function unableToGetHolidays() {
        return new static('There was a problem while getting the holidays');
    }
}