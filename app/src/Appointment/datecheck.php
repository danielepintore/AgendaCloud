<?php

class DateCheck {
    public static function isValidDate($date) {
        require(realpath(dirname(__FILE__, 3)) . '/config/config.php');
        $date = new DateTime($date);
        $maxDate = new DateTime();
        $now = new DateTime();
        // set the time part to zero
        $date->setTime(0, 0, 0);
        $maxDate->setTime(0, 0, 0);
        $now->setTime(0, 0, 0);
        if ($date <= $maxDate->add(new DateInterval("P" . $config["calendar"]["max_future_day"] . "D")) &&
            $date >= $now) {
            // the date shouldn't be in the past and cant'b be bigger than maxdate
            return true;
        } else {
            throw new Exception("The date is invalid");
        }
    }
}