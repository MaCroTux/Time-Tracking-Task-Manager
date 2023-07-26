<?php

namespace Tracking\Infrastructure\OutputInterface;

use Tracking\Domain\Repository\OutPutOInterface;

class ConsoleOutput implements OutPutOInterface
{
    private string $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function write(string $message): void
    {
        $this->message .= $message;
    }

    public function writeNl(string $message): void
    {
        $this->message .= $message . PHP_EOL;
    }

    public function addEOL(): void
    {
        $this->message .= PHP_EOL;
    }

    public function read(): string
    {
        return $this->message;
    }
}
