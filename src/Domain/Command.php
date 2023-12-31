<?php

namespace Tracking\Domain;

interface Command
{
    public function geyHelpMessage(): string;
    public function getName(): string;
    public function __invoke(array $inputs): void;
    public function isDefault(): bool;
}
