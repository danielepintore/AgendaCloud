<?php

class Interval {
    private DateTime $startTime;
    private DateTime $endTime;
    private DateInterval $serviceDuration;
    private DateInterval $waitInterval;

    /**
     * @param string $startTime
     * @param DateInterval $serviceDuration
     * @param DateInterval $waitInterval
     * @throws DateException
     */
    public function __construct(string $startTime, DateInterval $serviceDuration, DateInterval $waitInterval) {
        try {
            $this->startTime = new DateTime($startTime);
            $this->endTime = new DateTime($startTime);
        } catch (Exception $e){
            throw DateException::wrongStartOrEndTime();
        }
        $this->endTime->add($serviceDuration)->add($waitInterval);
        $this->serviceDuration = $serviceDuration;
        $this->waitInterval = $waitInterval;
    }

    /**
     * @return string
     */
    public function getStartTime(): string {
        return $this->startTime->format('H:i');
    }

    /**
     * @return string
     */
    public function getEndTime(): string {
        return $this->endTime->format('H:i');
    }

    /**
     * @return DateInterval
     */
    public function getServiceDuration(): DateInterval {
        return $this->serviceDuration;
    }

    /**
     * @return DateInterval
     */
    public function getWaitInterval(): DateInterval {
        return $this->waitInterval;
    }

    /**
     * @throws DateException
     * @return array{
     *     startTime: string,
     *     endTime: string
     * }
     * Gets and array containing the info about the interval
     */
    public function getArray(): array {
        try {
            $endTime = new DateTime($this->endTime->format('H:i'));
        } catch (Exception $e) {
            throw DateException::wrongStartOrEndTime();
        }
        // we remove the wait time because the client shouldn't see it
        return array("startTime" => $this->startTime->format('H:i'), "endTime" => $endTime->sub($this->waitInterval)->format('H:i'));
    }
}