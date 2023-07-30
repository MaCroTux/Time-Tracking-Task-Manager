<?php

namespace Tracking\Domain\Entity;

use Tracking\Domain\Service\AcumulateTimeFromPrevTaskService;

class Task
{
    private DateTime $date;
    private string $task;
    private ?string $timeAccumulated;

    public function __construct(DateTime $date, string $task, ?string $timeAccumulated = null)
    {
        $this->date = $date;
        $this->task = $task;
        $this->timeAccumulated = $timeAccumulated;
    }

    public static function fromPreviouslyDateAndTaskData(?string $prevDate, array $task): self
    {
        $date = DateTime::fromString($task['date']);
        $dateWeek = $task['dateWeek'];
        $tracking = $task['tracking'];

        if ($prevDate !== null) {
            $acumulateTimeFromPrevTaskService = new AcumulateTimeFromPrevTaskService();
            $timeAccumulated = $acumulateTimeFromPrevTaskService->__invoke($prevDate, $date);
            return new self(
                $date,
                "[$dateWeek] $tracking",
                $timeAccumulated
            );
        }

        return new self(
            $date,
            "[$dateWeek] $tracking"
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            $this->task,
            $this->timeAccumulated,
            $this->date->format(),
        ];
    }

    public function __toString(): string
    {
        return "$this->task ($this->timeAccumulated)";
    }

    public function getDateString(): string
    {
        return $this->date->format();
    }

    public function getDescription(): string
    {
        return $this->task;
    }
}
