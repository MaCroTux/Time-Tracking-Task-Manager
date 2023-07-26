<?php

namespace Tracking\Infrastructure;

use DateTimeImmutable;
use DateTimeInterface;
use Tracking\Domain\DateRepository;
use Tracking\Domain\DateTime;

class JsonDateRepository implements DateRepository
{
    private const FILENAME = './dates.json';

    public function readAll(?DateTime $now = null): array
    {
        if (!file_exists(JsonDateRepository::FILENAME)) {
            return [];
        }

        if ($now !== null) {
            $all = $this->jsonDecodeFile(JsonDateRepository::FILENAME);

            return array_filter(
                $all,
                function ($date) use ($now) {
                    $dateTime = DateTimeImmutable::createFromFormat(
                        DateTimeInterface::ATOM,
                        $date['date']
                    );
                    $now = DateTimeImmutable::createFromFormat(
                        DateTimeInterface::ATOM,
                        $now->format()
                    );

                    return $dateTime->diff($now)->d === 0;
                }
            );
        }

        return $this->jsonDecodeFile(JsonDateRepository::FILENAME);
    }

    public function save(DateTime $dateTime, string $dateTracking): void
    {
        $data = [];
        if (file_exists(JsonDateRepository::FILENAME)) {
            $data = json_decode(
                file_get_contents(JsonDateRepository::FILENAME),
                true
            );
        }

        $data[$dateTime->format()] = [
            'dateWeek' => $dateTime->__toString(),
            'date' => $dateTime->format(),
            'tracking' => $dateTracking,
        ];

        file_put_contents(JsonDateRepository::FILENAME, json_encode($data));
    }

    private function jsonDecodeFile(string $fileName): array
    {
        if (!file_exists($fileName)) {
            return [];
        }

        return json_decode(
            file_get_contents($fileName),
            true
        );
    }
}
