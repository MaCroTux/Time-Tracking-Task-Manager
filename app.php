<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tracking\Application\ShowTaskUseCase;
use Tracking\Domain\AcumulateTimeFromPrevTaskService;
use Tracking\Domain\ListCommand;
use Tracking\Domain\CommandFinder;
use Tracking\Domain\DateTime;
use Tracking\Infrastructure\ConsoleOutput;
use Tracking\Infrastructure\JsonDateRepository;

// ---- MAIN ----
array_shift($argv);
$input = implode(' ', $argv);

$outPut = new ConsoleOutput("");
$dateRepository = new JsonDateRepository();
$time = DateTime::now();
$commandFinder = new CommandFinder();

$listCommand = new ListCommand(
    new AcumulateTimeFromPrevTaskService(),
    $dateRepository,
    $time,
    $outPut
);
$commandFinder->addCommand($listCommand);

$showTaskUseCase = new ShowTaskUseCase($dateRepository, $outPut, $commandFinder);
$showTaskUseCase->__invoke($time, $input);
echo $outPut->read();
