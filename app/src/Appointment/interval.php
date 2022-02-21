<?php
class Interval {
    private $startTime;
    private $endTime;
    private $serviceDuration;
    private $waitInterval;

    /**
     * @param string $startTime
     * @param DateInterval $serviceDuration
     * @param DateInterval $waitInterval
     */
    public function __construct(string $startTime, DateInterval $serviceDuration, DateInterval $waitInterval) {
        $this->startTime = new DateTime($startTime);
        $this->endTime = new DateTime($startTime);
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
     * @return array
     */
    public function getArray(){
        $endTime = new DateTime($this->endTime->format('H:i'));
        // we remove the wait time because the client shouldn't see it
        return array("start_time" => $this->startTime->format('H:i'), "end_time" => $endTime->sub($this->waitInterval)->format('H:i'));
    }
}