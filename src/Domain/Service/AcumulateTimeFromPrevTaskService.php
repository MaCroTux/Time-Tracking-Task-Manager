<?php

namespace Tracking\Domain\Service;

use Tracking\Domain\Entity\DateTime;

class AcumulateTimeFromPrevTaskService
{
    public function __invoke(string $prevDate, DateTime $dateNow): string
    {
        $timeAccumulated = "";

        if (empty($prevDate)) {
            return "";
        }

        $datePrev = DateTime::fromString($prevDate);
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
