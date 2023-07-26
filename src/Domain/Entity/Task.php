<?php

namespace Tracking\Domain\Entity;

use Tracking\Domain\Service\AcumulateTimeFromPrevTaskService;

class Task
{
    private string $task;
    private ?string $timeAccumulated;

    public function __construct(string $task, ?string $timeAccumulated = null)
    {
        $this->task = $task;
        $this->timeAccumulated = $timeAccumulated;
    }

    public static function build($prevDate, $date, $task): self
    {
        if ($prevDate !== null) {
            $acumulateTimeFromPrevTaskService = new AcumulateTimeFromPrevTaskService();
            $timeAccumulated = $acumulateTimeFromPrevTaskService->__invoke($prevDate, $date);
            return new self("[{$task['dateWeek']}] {$task['tracking']}", $timeAccumulated);
        }

        return new self("[{$task['dateWeek']}] {$task['tracking']}");
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            $this->task,
            $this->timeAccumulated
        ];
    }

    public function __toString(): string
    {
        return "$this->task ($this->timeAccumulated)";
    }
}
