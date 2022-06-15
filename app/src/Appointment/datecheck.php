<?php

class DateCheck {
    /**
     * @param $date
     * @return bool
     * @throws DateException
     */
    public static function isValidDate($date, $isUserAuthenticated = false) {
        $config = Config::getConfig();
        try {
            $date = new DateTime($date);
            $now = new DateTime();
            $maxDate = new DateTime();
            // set the time part to zero
            $date->setTime(0, 0, 0);
            $maxDate->setTime(0, 0, 0);
            $now->setTime(0, 0, 0);
            if ($isUserAuthenticated && $date >= $now){
                return true;
            }
        } catch (Exception $e) {
            throw DateException::invalidData();
        }

        try {
            $maxDateInterval = new DateInterval("P" . $config->calendar->max_future_day . "D");
        } catch (Exception $e) {
            throw DateException::wrongIntervalString();
        }
        if ($date <= $maxDate->add($maxDateInterval) && $date >= $now) {
            // the date shouldn't be in the past and cant'b be bigger than maxdate
            return true;
        } else {
            throw DateException::invalidData();
        }
    }

    public static function isToday($date) {
        try {
            $date = new DateTime($date);
        } catch (Exception $e) {
            throw DateException::invalidData();
        }
        $now = new DateTime();
        $date->setTime(0, 0, 0);
        $now->setTime(0, 0, 0);
        if ($now == $date) {
            return true;
        } else {
            return false;
        }
    }
}