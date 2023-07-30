<?php

namespace Tracking\Domain\Service;

use Tracking\Domain\Command;
use Tracking\Domain\Entity\DateTime;
use Tracking\Domain\Entity\Task;
use Tracking\Domain\Repository\DateRepository;
use Tracking\Domain\Repository\OutPutOInterface;

class EditCommandService implements Command
{
    private const TASK_MESSAGE = 'tracking';
    private const COMMAND = "-e";

    private AcumulateTimeFromPrevTaskService $acumulateTimeFromPrevTaskService;
    private DateRepository $dateRepository;
    private DateTime $dateTime;
    private OutPutOInterface $outPut;

    public function __construct(
        AcumulateTimeFromPrevTaskService $acumulateTimeFromPrevTaskService,
        DateRepository                   $dateRepository,
        DateTime                         $dateTime,
        OutPutOInterface                 $outPut,
    ) {
        $this->acumulateTimeFromPrevTaskService = $acumulateTimeFromPrevTaskService;
        $this->dateRepository = $dateRepository;
        $this->dateTime = $dateTime;
        $this->outPut = $outPut;
    }

    public function getName(): string
    {
        return self::COMMAND;
    }

    public function __invoke(array $inputs): void
    {
        if (count($inputs) !== 2) {
            $this->outPut->writeNl("El comando -e necesita dos parámetros.");
            $this->outPut->writeNl("Ejemplo -e 1.");
            return;
        }

        [$command, $index] = $inputs;
        unset($command);
        $this->outPut->addEOL();

        $taskForEditRaw = $this->getTask($this->dateTime, (int)$index);

        if ($taskForEditRaw === null) {
            $this->outPut->writeNl("No existe la tarea con el índice $index.");
            return;
        }

        $taskMessage = $taskForEditRaw[self::TASK_MESSAGE] ?? '';
        if (empty($taskMessage)) {
            $this->outPut->writeNl("Sin tareas registradas.");
            return;
        }


        $this->outPut->writeNl("1. $taskMessage");
        $this->outPut->write("[Nuevo nombre] 1. ");

        echo $this->outPut->read();

        $newNameForTask = readline();

        if (empty($newNameForTask)) {
            $this->outPut->writeNl("No se ha cambiado el nombre de la tarea.");
            return;
        }

        $this->dateRepository->update(
            DateTime::fromString($taskForEditRaw['date']),
            $newNameForTask
        );

        $this->outPut->writeNl("Tarea modificada.");
    }

    public function geyHelpMessage(): string
    {
        $command = self::COMMAND;

        return "$command Lista todas las tareas registradas.";
    }

    private function lastTimeTracking(): string
    {
        $all = $this->dateRepository->readAll($this->dateTime);
        $dates = array_keys($all);
        return $this->acumulateTimeFromPrevTaskService->__invoke(
            end($dates),
            date(DATE_ATOM)
        );
    }

    /**
     * @param DateTime $dateTime
     * @param int $index
     * @return null|array
     */
    private function getTask(DateTime $dateTime, int $index): ?array
    {
        $tasks = $this->dateRepository->readAll($dateTime);

        $i = 1;
        foreach ($tasks as $task) {
            if ($i === $index) {
                return $task;
            }
            $i++;
        }

        return null;
    }

    public function isDefault(): bool
    {
        return true;
    }
}
