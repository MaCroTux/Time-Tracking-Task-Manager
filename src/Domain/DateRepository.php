<?php

namespace Tracking\Domain;

interface DateRepository
{
    public function save(DateTime $dateTime, string $dateTracking): void;
    public function readAll(?DateTime $now = null): array;
}
