<?php

use Admin\User;

class Slot {

    /**
     * @throws ServiceException
     * @throws DatabaseException
     * @throws DateException
     * @throws EmployeeException
     * @throws Exception
     * @return array{
     *     array{
     *      startTime: string,
     *      endTime: string
     *  }
     * }
     * Returns a list of slots available for booking given a service id, an employee id and a date
     */
    public static function getSlots(Database $db, $serviceId, $employeeId, $dateStr, $isUserAuthenticated = false): array {
        require_once(realpath(dirname(__FILE__, 3)) . '/vendor/autoload.php');
        DateCheck::isValidDate($dateStr, $isUserAuthenticated); // Throws an exception if the date is invalid
        $weekDay = (new DateTime($dateStr))->format('N');
        $workingTimes = workingTimes::getWorkingTimes($db, $weekDay, $dateStr, $serviceId, $employeeId);
        // check if the employee is active
        if (!\Admin\Employee::isActive($db, $employeeId)) {
            throw EmployeeException::employeeIsNotActive();
        }
        // get service data from the database
        $service = new Service($db, $serviceId);
        $bookedSlots = Appointment::getBookedAppointment($db, $employeeId, $dateStr);
        // if the user is authenticated we can disable the BookableUntil time generatedSlot
        if($isUserAuthenticated){
            $service->setBookableUntil(0);
        }

        // generation of available slots
        try {
            $intervalTime = $service->getDuration() + $service->getWaitTime();
            $serviceDuration = new DateInterval("PT" . $service->getDuration() . "M");
            $totalInterval = new DateInterval("PT" . $intervalTime . "M");
            $waitInterval = new DateInterval("PT" . $service->getWaitTime() . "M");
            $bookableUntilInterval = new DateInterval("PT" . $service->getBookableUntil() . "M");
        } catch (ServiceException $serviceException){
            //todo test that
            throw new ServiceException(previous: $serviceException);
        } catch (Exception $e) {
            throw DateException::wrongIntervalString();
        }
        $serviceStartTime = DateTime::createFromFormat("Y-m-d G:i", $dateStr . " " . $workingTimes["startTime"]);
        $serviceEndTime = DateTime::createFromFormat("Y-m-d G:i", $dateStr . " " . $workingTimes["endTime"]);
        $currentTime = new DateTime();
        $availableSlots = [];
        do {
            $generatedSlot = new Interval($serviceStartTime->format('H:i'), $serviceDuration, $waitInterval);
            // check if the service can be added due it's time, because if it's already too late we can't add it
            $slotStartTime = clone $serviceStartTime; // creates a new object
            // check if the slot can be booked checking if the slot finish before the end of the service time and
            // if the current time is smaller than the start time of the slot
            if ($currentTime >= $slotStartTime->sub($bookableUntilInterval) ||
                $generatedSlot->getEndTime() > $serviceEndTime->format('H:i')) {
                // slot can't be booked
                $serviceStartTime->add($totalInterval);
                continue;
            }
            $isAvailable = true;
            foreach ($bookedSlots as $appointment) {
                if ($generatedSlot->getStartTime() < $appointment["startTime"] && $generatedSlot->getEndTime() <= $appointment["startTime"] ||
                    $generatedSlot->getStartTime() >= $appointment["endTime"]) {
                    // the slot can be available
                } else {
                    // the slot isn't available
                    $isAvailable = false;
                    break;
                }
            }
            if ($isAvailable) {
                // we can add the slot
                // wait time isn't visible to the user because the getArray() function removes it
                $availableSlots[] = $generatedSlot->getArray();
            }
            // go to the next slot
            $serviceStartTime->add($totalInterval);
        } while ($serviceStartTime < $serviceEndTime);
        // if there is a holidays remove some slots
        // get holiday start and end time
        $holidayTimes = workingTimes::getHolidayTimes($db, $weekDay, $dateStr, $serviceId, $employeeId);
        return self::removeHolidayTime($availableSlots, $holidayTimes);
    }

/**
 * @return array{
 *     array{
 *      startTime: string,
 *      endTime: string
 *  }
 * }
 * Given a list of holidays remove the slots not compatible with them
 */
    private static function removeHolidayTime($slots, $holidays): array {
        $slotsWithHolidays = [];
        foreach ($holidays as $holiday) {
            foreach ($slots as $slot) {
                // add only the slots that aren't included in the holiday timeframe
                if ($slot["startTime"] <= $holiday['startTime'] && $slot['endTime'] <= $holiday['startTime']) {
                    $slotsWithHolidays[] = $slot;
                } elseif ($slot["startTime"] >= $holiday['endTime']) {
                    $slotsWithHolidays[] = $slot;
                }
            }
            $slots = $slotsWithHolidays;
            $slotsWithHolidays = [];
        }
        return $slots;
    }
}