<?php

namespace Tracking\Domain;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateTime
{
    private const DATE_FORMAT = 'l H:m:s';
    private const TIME_ZONE = 'Europe/Madrid';
    private DateTimeInterface $dateTimeImmutable;

    private function __construct(
        DateTimeInterface $dateTimeImmutable
    ) {
        $this->dateTimeImmutable = $dateTimeImmutable;
    }

    public static function fromString(string $date): self
    {
        return new self(
            DateTimeImmutable::createFromFormat(
                DateTimeImmutable::ATOM,
                $date,
                new DateTimeZone(DateTime::TIME_ZONE)
            )
        );
    }

    public function diff(DateTime $dateTime): DateInterval
    {
        return $this->dateTimeImmutable->diff($dateTime->dateTimeImmutable);
    }

    public static function now(): self
    {
        return new self(
            new DateTimeImmutable(
                "now",
                new DateTimeZone(DateTime::TIME_ZONE)
            )
        );
    }

    public function format(): string
    {
        return $this->dateTimeImmutable->format(DateTimeImmutable::ATOM);
    }

    public function __toString(): string
    {
        return $this->dateTimeImmutable->format(DateTime::DATE_FORMAT);
    }
}
