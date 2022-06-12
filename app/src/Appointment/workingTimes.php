<?php
class workingTimes {
    /**
     * @param Database $db
     * @param $day
     * @param $serviceId
     * @param $employeeId
     * @return array|string[]
     * Gets the interval of working time available considering the service working time and the
     * employee working time
     */
    public static function getWorkingTimes(Database $db, $day, $dateStr, $serviceId, $employeeId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $serviceWorkTimes = \Admin\Services::getDayWorkingTimes($db, $day, $serviceId);
        $customServiceWorkTimes = \Admin\Services::getDayCustomWorkingTimes($db, $dateStr, $serviceId);
        $employeeWorkTimes = \Admin\Employee::getDayWorkingTimes($db, $day,  $employeeId);
        $customEmployeeWorkTimes = \Admin\Employee::getDayCustomWorkingTimes($db, $dateStr,  $employeeId);
        if (!empty($customEmployeeWorkTimes)){
            $employeeWorkTimes = $customEmployeeWorkTimes;
        }
        if (!empty($customServiceWorkTimes)){
            $serviceWorkTimes = $customServiceWorkTimes;
        }
        if ($serviceWorkTimes["startTime"] >= $employeeWorkTimes["endTime"] || $employeeWorkTimes["startTime"] >= $serviceWorkTimes["endTime"]){
            $startTime = "00:00";
            $endTime = "00:00";
            return array('startTime' => $startTime, 'endTime' => $endTime);
        }
        if ($serviceWorkTimes["startTime"] >= $employeeWorkTimes["startTime"] && $serviceWorkTimes["startTime"] <= $employeeWorkTimes["endTime"]) {
            $startTime = $serviceWorkTimes["startTime"];
        } else {
            $startTime = $employeeWorkTimes["startTime"];
        }
        $endTime = min($employeeWorkTimes["endTime"], $serviceWorkTimes["endTime"]);
        return array("startTime" => $startTime, "endTime" => $endTime);
    }

    public static function getHolidayTimes(Database $db, $day, $dateStr, $serviceId, $employeeId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $serviceHolidayTimes = \Admin\Services::getDayHolidayTimes($db, $day, $serviceId);
        $customServiceHolidayTimes = \Admin\Services::getDayCustomHolidayTimes($db, $dateStr, $serviceId);
        $employeeHolidayTimes = \Admin\Employee::getDayHolidayTimes($db, $day,  $employeeId);
        $customEmployeeHolidayTimes = \Admin\Employee::getDayCustomHolidayTimes($db, $dateStr,  $employeeId);
        //print("serviceHolidayTimes:\n");
        //var_dump($serviceHolidayTimes);
        //print("employeeHolidayTimes:\n");
        //var_dump($employeeHolidayTimes);
        //print("customServiceHolidayTimes:\n");
        //var_dump($customServiceHolidayTimes);
        //print("customEmployeeHolidayTimes:\n");
        //var_dump($customEmployeeHolidayTimes);
        if (!empty($customEmployeeHolidayTimes)){
            $employeeHolidayTimes = $customEmployeeHolidayTimes;
        }
        if (!empty($customServiceHolidayTimes)){
            $serviceHolidayTimes = $customServiceHolidayTimes;
        }
        if ($serviceHolidayTimes["startTime"] >= $employeeHolidayTimes["endTime"] || $employeeHolidayTimes["startTime"] >= $serviceHolidayTimes["endTime"]){
            $startTime = "00:00";
            $endTime = "00:00";
            return [['startTime' => $startTime, 'endTime' => $endTime]];
        }
        if ($serviceHolidayTimes["startTime"] >= $employeeHolidayTimes["startTime"] && $serviceHolidayTimes["startTime"] <= $employeeHolidayTimes["endTime"]) {
            $startTime = $serviceHolidayTimes["startTime"];
        } else {
            $startTime = $employeeHolidayTimes["startTime"];
        }
        $endTime = min($employeeHolidayTimes["endTime"], $serviceHolidayTimes["endTime"]);
        return [["startTime" => $startTime, "endTime" => $endTime]];
    }

    public static function getWorkingTimesBackup(Database $db, $day, $serviceId, $employeeId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $serviceWorkTimes = \Admin\Services::getDayWorkingTimes($db, $day, $serviceId);
        $employeeWorkTimes = \Admin\Employee::getDayWorkingTimes($db,$day,  $employeeId);
        if ($serviceWorkTimes["startTime"] >= $employeeWorkTimes["endTime"] || $employeeWorkTimes["startTime"] >= $serviceWorkTimes["endTime"]){
            $startTime = "00:00";
            $endTime = "00:00";
            return array('startTime' => $startTime, 'endTime' => $endTime);
        }
        if ($serviceWorkTimes["startTime"] >= $employeeWorkTimes["startTime"] && $serviceWorkTimes["startTime"] <= $employeeWorkTimes["endTime"]) {
            $startTime = $serviceWorkTimes["startTime"];
        } else {
            $startTime = $employeeWorkTimes["startTime"];
        }
        $endTime = min($employeeWorkTimes["endTime"], $serviceWorkTimes["endTime"]);
        return array("startTime" => $startTime, "endTime" => $endTime);
    }
}