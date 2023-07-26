<?php

namespace Tracking\Domain;

class ListCommand implements Command
{
    private const TASK_MESSAGE = 0;
    private const TASK_TIME = 1;
    private const COMMAND = "-l";
    private const CURRENT_INDEX = 0;
    private const NEXT_INDEX = 1;

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

    public function __invoke(): void
    {
        $this->outPut->addEOL();

        $lastDate = getList($this->dateRepository, $this->dateTime);

        $taskListRaw = array_map(
            fn (Task $task) => $task->toArray(),
            $lastDate
        );

        $taskMessage = $taskListRaw[self::CURRENT_INDEX][self::TASK_MESSAGE] ?? '';
        $timeAccumulated = $taskListRaw[self::NEXT_INDEX][self::TASK_TIME] ?? '';
        $this->outPut->writeNl("1. {$taskMessage} ({$timeAccumulated})");

        foreach ($taskListRaw as $index => $item) {
            if ($index === 0) {
                continue;
            }

            $taskNumber = $index + 1;
            $taskMessage = $item[self::TASK_MESSAGE] ?? '';
            $timeAccumulated = $taskListRaw[$index + 1][self::TASK_TIME]
                ?? "{$this->lastTimeTracking()}, llevas actualmente"
            ;

            $this->outPut->writeNl("{$taskNumber}. {$taskMessage} ({$timeAccumulated})");
        }
    }

    public function geyHelpMessage(): string
    {
        $command = self::COMMAND;

        return "{$command} Lista todas las tareas registradas.";
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
}
