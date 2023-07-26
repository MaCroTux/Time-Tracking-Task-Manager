<?php

namespace Tracking\Domain\Repository;

interface OutPutOInterface
{
    public function write(string $message): void;
    public function writeNl(string $message): void;
    public function addEOL(): void;
    public function read(): string;
}
