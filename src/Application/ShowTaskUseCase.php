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
        array $inputs
    ): void {
        if (empty($inputs)) {
            $this->commandFinder->getDefaultCommand()?->__invoke($inputs);
            $this->output->addEOL();
            $this->output->writeNl("Escribe --help para más información");
            return;
        }

        if ($this->isHelperCommand($inputs)) {
            $this->output->writeNl("Lista de comandos:");
            $this->output->addEOL();
            $this->commandFinder->help($this->output);
            return;
        }

        if ($this->isNotCommandParameter($inputs)) {
            $this->saveTask($this->dateRepository, $dateTime, $inputs, $this->output);

            return;
        }

        $command = $this->commandFinder->findCommand($inputs);
        $command?->__invoke($inputs);
    }

    private function isHelperCommand(array $inputs): bool
    {
        $input = array_shift($inputs);

        return str_starts_with($input, '--help');
    }

    private function saveTask(
        DateRepository $dateRepository,
        DateTime $dateTime,
        array $inputs,
        OutPutOInterface $output
    ): void {
        $input = implode(' ', $inputs);
        $dateRepository->save($dateTime, $input);
        $output->write("[{$dateTime->__toString()}]: $input");
        $output->addEOL();
        $output->addEOL();
        $output->write("Tarea registrada !");
        $output->addEOL();
    }

    private function isNotCommandParameter(array $inputs): bool
    {
        $input = array_shift($inputs);

        return !str_starts_with($input, '-');
    }
}
