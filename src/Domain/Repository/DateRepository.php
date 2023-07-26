<?php

namespace Tracking\Domain\Repository;

use Tracking\Domain\Entity\DateTime;

interface DateRepository
{
    public function save(DateTime $dateTime, string $dateTracking): void;
    public function readAll(?DateTime $now = null): array;
}
