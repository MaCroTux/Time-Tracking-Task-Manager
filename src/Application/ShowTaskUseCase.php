<?php

namespace Tracking\Application;

use Tracking\Domain\CommandFinder;
use Tracking\Domain\Entity\DateTime;
use Tracking\Domain\Repository\DateRepository;
use Tracking\Domain\Repository\OutPutOInterface;

class ShowTaskUseCase
{
    private DateRepository $dateRepository;
    private OutPutOInterface $output;
    private CommandFinder $commandFinder;

    public function __construct(
        DateRepository   $dateRepository,
        OutPutOInterface $output,
        CommandFinder    $commandFinder,
    ) {
        $this->dateRepository = $dateRepository;
        $this->output = $output;
        $this->commandFinder = $commandFinder;
    }

    public function __invoke(
        DateTime $dateTime,
        string $input
    ): void {
        if (empty($input)) {
            $this->output->writeNl("-help para más información");
            return;
        }

        if ($this->isHelperCommand($input)) {
            $this->output->writeNl("Lista de comandos:");
            $this->output->addEOL();
            $this->commandFinder->help($this->output);
            return;
        }

        if ($this->isNotCommandParameter($input)) {
            $this->saveTask($this->dateRepository, $dateTime, $input, $this->output);

            return;
        }

        $command = $this->commandFinder->findCommand($input);
        $command?->__invoke();
    }

    private function isHelperCommand(string $input): bool
    {
        return str_starts_with($input, '-help');
    }

    private function saveTask(
        DateRepository $dateRepository,
        DateTime $dateTime,
        string $input,
        OutPutOInterface $output
    ): void {
        $dateRepository->save($dateTime, $input);
        $output->write("[{$dateTime->__toString()}]: $input");
        $output->addEOL();
        $output->addEOL();
        $output->write("Tarea registrada !");
        $output->addEOL();
    }

    private function isNotCommandParameter(string $input): bool
    {
        return !str_starts_with($input, '-');
    }
}
