<?php

/**
 * This class manages the working times of an employee or a service
 */
class workingTimes {
    /**
     * @param Database $db
     * @param $day
     * @param $dateStr
     * @param $serviceId
     * @param $employeeId
     * @return array{
     *     startTime: string,
     *     endTime: string
     * }
     * @throws DatabaseException
     * Gets the interval of working time available considering the service working time and the
     * employee working time
     */
    public static function getWorkingTimes(Database $db, $day, $dateStr, $serviceId, $employeeId){
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $serviceWorkTimes = \Admin\Services::getDayWorkingTimes($db, $day, $serviceId);
        $customServiceWorkTimes = \Admin\Services::getDayCustomWorkingTimes($db, $dateStr, $serviceId);
        $employeeWorkTimes = \Admin\Employee::getDayWorkingTimes($db, $day,  $employeeId);
        $customEmployeeWorkTimes = \Admin\Employee::getDayCustomWorkingTimes($db, $dateStr,  $employeeId);
        if (!empty($customServiceWorkTimes)){
            $serviceWorkTimes = $customServiceWorkTimes;
        }
        if (!empty($customEmployeeWorkTimes)){
            $employeeWorkTimes = $customEmployeeWorkTimes;
        }
        return self::calculateInterval($serviceWorkTimes, $employeeWorkTimes);

    }

    /**
     * @param Database $db
     * @param $day
     * @param $dateStr
     * @param $serviceId
     * @param $employeeId
     * @return array
     * @throws DatabaseException
     * Gets the interval of free time available considering the service free time and the
     * employee free time
     */
    public static function getHolidayTimes(Database $db, $day, $dateStr, $serviceId, $employeeId): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        $serviceHolidayTimes = \Admin\Services::getDayHolidayTimes($db, $day, $serviceId);
        $customServiceHolidayTimes = \Admin\Services::getDayCustomHolidayTimes($db, $dateStr, $serviceId);
        $employeeHolidayTimes = \Admin\Employee::getDayHolidayTimes($db, $day,  $employeeId);
        $customEmployeeHolidayTimes = \Admin\Employee::getDayCustomHolidayTimes($db, $dateStr,  $employeeId);
        if (!empty($customServiceHolidayTimes)){
            $serviceHolidayTimes = $customServiceHolidayTimes;
        }
        if (!empty($customEmployeeHolidayTimes)){
            $employeeHolidayTimes = $customEmployeeHolidayTimes;
        }
        return [self::calculateInterval($serviceHolidayTimes, $employeeHolidayTimes)];
    }

    /**
     * @param $serviceTimes
     * @param $employeeTimes
     * @return array{
     *     startTime: string,
     *     endTime: string
     * }
     * Calculate the interval matching the service time and the employee time
     */
    private static function calculateInterval($serviceTimes, $employeeTimes): array {
        if (empty($serviceTimes) || empty($employeeTimes)) {
            return ['startTime' => "00:00", 'endTime' => "00:00"];
        }
        if ($serviceTimes["startTime"] >= $employeeTimes["endTime"] || $employeeTimes["startTime"] >= $serviceTimes["endTime"]){
            $startTime = "00:00";
            $endTime = "00:00";
            return ['startTime' => $startTime, 'endTime' => $endTime];
        }
        if ($serviceTimes["startTime"] >= $employeeTimes["startTime"] && $serviceTimes["startTime"] <= $employeeTimes["endTime"]) {
            $startTime = $serviceTimes["startTime"];
        } else {
            $startTime = $employeeTimes["startTime"];
        }
        $endTime = min($employeeTimes["endTime"], $serviceTimes["endTime"]);
        return ["startTime" => $startTime, "endTime" => $endTime];
    }

}