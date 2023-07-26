<?php

namespace Tracking\Domain\Service;

use Tracking\Domain\Entity\DateTime;

class AcumulateTimeFromPrevTaskService
{
    public function __invoke(string $prevDate, string $date): string
    {
        $timeAccumulated = "";

        if (empty($prevDate)) {
            return "";
        }

        $datePrev = DateTime::fromString($prevDate);
        $dateNow = DateTime::fromString($date);
        $dateAccumulated = $datePrev->diff($dateNow);

        if ($dateAccumulated->h > 0) {
            $timeAccumulated .= "{$dateAccumulated->h}h ";
        }
        if ($dateAccumulated->i > 0) {
            $timeAccumulated .= "{$dateAccumulated->i}m";
        }

        return !empty($timeAccumulated)
            ? "+$timeAccumulated"
            : '+1m';
    }
}
