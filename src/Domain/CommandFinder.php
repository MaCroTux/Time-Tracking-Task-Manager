<?php

namespace Tracking\Domain;

use Tracking\Domain\Repository\OutPutOInterface;

class CommandFinder
{
    /** @var Command[] */
    private array $commands = [];

    public function help(OutPutOInterface $output): void
    {
        $help = '';
        foreach ($this->commands as $command) {
            $help .= $command->geyHelpMessage() . PHP_EOL;
        }

        $output->write($help);
    }

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
