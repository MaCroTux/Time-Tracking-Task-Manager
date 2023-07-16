<?php

namespace Tracking\Domain;

interface Command
{
    public function getName(): string;
    public function __invoke(): void;
}
