<?php

namespace Tracking\Domain\Repository;

use Tracking\Domain\Entity\DateTime;
use Tracking\Domain\Entity\Task;

interface DateRepository
{
    public function save(DateTime $dateTime, string $dateTracking): void;
    /** @return Task[] */
    public function readAll(?DateTime $now = null): array;
    public function update(DateTime $dateTime, string $newNameForTask): void;
}
