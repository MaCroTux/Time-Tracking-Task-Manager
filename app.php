<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tracking\Application\ShowTaskUseCase;
use Tracking\Domain\CommandFinder;
use Tracking\Domain\Entity\DateTime;
use Tracking\Domain\Service\AcumulateTimeFromPrevTaskService;
use Tracking\Domain\Service\ListCommandService;
use Tracking\Infrastructure\OutputInterface\ConsoleOutput;
use Tracking\Infrastructure\Persistence\JsonDateRepository;

array_shift($argv);
$input = implode(' ', $argv);
$time = DateTime::now();

$outPut = new ConsoleOutput("");
$dateRepository = new JsonDateRepository($time);
$commandFinder = new CommandFinder();

$listCommand = new ListCommandService(
    new AcumulateTimeFromPrevTaskService(),
    $dateRepository,
    $time,
    $outPut
);
$commandFinder->addCommand($listCommand);

$showTaskUseCase = new ShowTaskUseCase($dateRepository, $outPut, $commandFinder);
$showTaskUseCase->__invoke($time, $input);
echo $outPut->read();
