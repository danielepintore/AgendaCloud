<?php

class DateCheck {
    /**
     * @param $date
     * @return bool
     * @throws DataException
     */
    public static function isValidDate($date, $isUserAuthenticated = false) {
        $config = Config::getConfig();
        try {
            $date = new DateTime($date);
            if ($isUserAuthenticated){
                return true;
            }
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

    /**
     * SELECT Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoChiusuraServizio WHERE (Data = ? AND Servizio_id = ?) UNION SELECT Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoLiberoDipendente WHERE (Data = ? AND Dipendente = ?)
     */

    /**
     * @throws DatabaseException
     * Check if a date is a holiday date for a specific service, and returns the array associated to the query
     * in order to generate the correct slots
     */
    public static function getHolidayInfo(Database $db, $dateString, $serviceId, $employeeId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $sql = 'SELECT Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoChiusuraServizio WHERE (Data = ? AND Servizio_id = ?) UNION SELECT Data, TIME_FORMAT(OraInizio, "%H:%i") AS OraInizio, TIME_FORMAT(OraFine, "%H:%i") AS OraFine FROM GiornoLiberoDipendente WHERE (Data = ? AND Dipendente_id = ?)';
        $status = $db->query($sql, "sisi", $dateString, $serviceId, $dateString, $employeeId);
        $result = $db->getResult();
        if ($status && $db->getAffectedRows() > 0){
            $holidays = [];
            foreach ($result as $r){
                $holidays[] = ["date" => $r['Data'], "startTime" => $r['OraInizio'], "endTime" => $r['OraFine']];
            }
            return $holidays;
        }
        return [];
    }
}