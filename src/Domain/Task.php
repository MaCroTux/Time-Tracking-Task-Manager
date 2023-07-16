<?php

namespace Tracking\Domain;

class Task
{
    private string $task;

    public function __construct(string $task)
    {
        $this->task = $task;
    }

    public static function build($prevDate, $date, $task): self
    {
        if ($prevDate !== null) {
            $acumulateTimeFromPrevTaskService = new AcumulateTimeFromPrevTaskService();
            $timeAccumulated = $acumulateTimeFromPrevTaskService->__invoke($prevDate, $date);
            return new self("[{$task['dateWeek']}] {$task['tracking']} ({$timeAccumulated})");
        }

        return new self("[{$task['dateWeek']}] {$task['tracking']}");
    }

    public function __toString(): string
    {
        return $this->task;
    }
}
