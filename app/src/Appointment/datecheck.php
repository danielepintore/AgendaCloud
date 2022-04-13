<?php

class DateCheck {
    /**
     * @param $date
     * @return bool
     * @throws DataException
     */
    public static function isValidDate($date) {
        $config = Config::getConfig();
        try {
            $date = new DateTime($date);
        } catch (Exception $e) {
            throw DataException::invalidData();
        }
        $maxDate = new DateTime();
        $now = new DateTime();
        // set the time part to zero
        $date->setTime(0, 0, 0);
        $maxDate->setTime(0, 0, 0);
        $now->setTime(0, 0, 0);
        try {
            $maxDateInterval = new DateInterval("P" . $config->calendar->max_future_day . "D");
        } catch (Exception $e) {
            throw DataException::wrongIntervalString();
        }
        if ($date <= $maxDate->add($maxDateInterval) && $date >= $now) {
            // the date shouldn't be in the past and cant'b be bigger than maxdate
            return true;
        } else {
            throw DataException::invalidData();
        }
    }

    public static function isToday($date) {
        try {
            $date = new DateTime($date);
        } catch (Exception $e) {
            throw DataException::invalidData();
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