<?php

namespace Tracking\Domain;

class ListCommand implements Command
{
    private string $command = "-l";
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
        return $this->command;
    }

    public function __invoke(): void
    {
        $this->outPut->addEOL();

        foreach (getList($this->dateRepository, $this->dateTime) as $item) {
            $this->outPut->writeNl($item->__toString());
        }

        $all = $this->dateRepository->readAll($this->dateTime);
        $dates = array_keys($all);
        $lastDate = $this->acumulateTimeFromPrevTaskService->__invoke(
            end($dates),
            date(DATE_ATOM)
        );

        if (!empty($lastDate)) {
            $this->outPut->write(PHP_EOL . "Llevas actualmente: {$lastDate}, en tu ultima tarea." . PHP_EOL);
        }
    }
}
