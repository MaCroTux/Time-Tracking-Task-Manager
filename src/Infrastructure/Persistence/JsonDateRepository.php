<?php

namespace Tracking\Infrastructure\Persistence;

use Tracking\Domain\Entity\DateTime;
use Tracking\Domain\Entity\Task;
use Tracking\Domain\Repository\DateRepository;

class JsonDateRepository implements DateRepository
{
    private const FILENAME = 'dates';
    private const DATE_TIME_STRING_RULES = 'Ymd';

    private DateTime $dateTime;

    public function __construct(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    private function getTimeFileName(): string
    {
        $timeName = $this->dateTime->formatWithRules(self::DATE_TIME_STRING_RULES);
        $fileName = self::FILENAME;

        return "./$fileName$timeName.json";
    }

    /** @return Task[] */
    public function readAll(?DateTime $now = null): array
    {
        if (!file_exists($this->getTimeFileName())) {
            return [];
        }

        return $this->jsonDecodeFile($this->getTimeFileName());
    }

    public function save(DateTime $dateTime, string $dateTracking): void
    {
        $data = [];
        if (file_exists($this->getTimeFileName())) {
            $data = json_decode(
                file_get_contents($this->getTimeFileName()),
                true
            );
        }

        $data[$dateTime->format()] = [
            'dateWeek' => $dateTime->__toString(),
            'date' => $dateTime->format(),
            'tracking' => $dateTracking,
        ];

        $this->saveFileData($data);
    }

    private function saveFileData(array $data): void
    {
        file_put_contents($this->getTimeFileName(), json_encode($data));
    }

    /** @return Task[] */
    private function jsonDecodeFile(string $fileName): array
    {
        if (!file_exists($fileName)) {
            return [];
        }

        $rawData = json_decode(
            file_get_contents($fileName),
            true
        );

        $list = [];
        $prevDate = null;
        foreach ($rawData as $data) {
            $list[] = Task::fromPreviouslyDateAndTaskData($prevDate, $data);
            $prevDate = $data['date'];
        }

        return $list;
    }

    public function update(DateTime $dateTime, string $newNameForTask): void
    {
        $data = [];
        if (file_exists($this->getTimeFileName())) {
            $data = json_decode(
                file_get_contents($this->getTimeFileName()),
                true
            );
        }

        $data[$dateTime->format()] = [
            'dateWeek' => $dateTime->__toString(),
            'date' => $dateTime->format(),
            'tracking' => $newNameForTask,
        ];

        $this->saveFileData($data);
    }
}
