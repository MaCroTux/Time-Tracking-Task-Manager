<?php

namespace Tracking\Domain;

class CommandFinder
{
    /** @var Command[] */
    private array $commands = [];

    public function addCommand(Command $command): void
    {
        $this->commands[] = $command;
    }

    public function findCommand(string $commandName): ?Command
    {
        foreach ($this->commands as $command) {
            if ($command->getName() === $commandName) {
                return $command;
            }
        }

        return null;
    }
}
