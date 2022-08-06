<?php

class DateCheck {
    /**
     * @param $date
     * @param bool $isUserAuthenticated
     * @return bool
     * @throws DateException
     * Returns true if the date is valid
     */
    public static function isValidDate($date, bool $isUserAuthenticated = false): bool {
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
            // the date shouldn't be in the past and can't be bigger than maxdate
            return true;
        } else {
            throw DateException::invalidData();
        }
    }

    /**
     * @param string $date
     * @return bool
     * @throws DateException
     * Returns true if the date passed is today
     */
    public static function isToday(string $date): bool {
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