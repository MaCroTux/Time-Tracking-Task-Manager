<?php

require_once __DIR__ . '/vendor/autoload.php';

use Tracking\Domain\AcumulateTimeFromPrevTaskService;
use Tracking\Domain\ListCommand;
use Tracking\Domain\CommandFinder;
use Tracking\Domain\DateRepository;
use Tracking\Domain\DateTime;
use Tracking\Domain\OutPutOInterface;
use Tracking\Domain\Task;
use Tracking\Infrastructure\ConsoleOutput;
use Tracking\Infrastructure\JsonDateRepository;

function jsonDecodeFile(string $fileName): array
{
    if (!file_exists($fileName)) {
        return [];
    }

    return json_decode(
        file_get_contents($fileName),
        true
    );
}
function read(): string
{
    $stream = fopen("/dev/stdin", "r");
    $input = fgets($stream, 255);
    fclose($stream);

    return $input;
}

function getList(DateRepository $dateRepository, DateTime $dateTime): array
{
    $dates = $dateRepository->readAll($dateTime);

    $list = [];
    $prevDate = null;
    foreach ($dates as $date => $task) {
        $list[] = Task::build($prevDate, $date, $task);
        $prevDate = $date;
    }

    return $list;
}

function showTasks(
    DateTime         $dateTime,
    DateRepository   $dateRepository,
    OutPutOInterface $output,
    CommandFinder    $commandFinder
): void {
    echo "[{$dateTime->__toString()}]: ";
    $input = trim(read());

    if (empty($input)) {
        return;
    }

    $command = $commandFinder->findCommand($input);
    if ($command) {
        $command->__invoke();

        return;
    }

    $dateRepository->save($dateTime, $input);
    $output->addEOL();
    $output->write("Tarea registrada !");
    $output->addEOL();
}

// ---- MAIN ----
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

showTasks(
    $time,
    $dateRepository,
    $outPut,
    $commandFinder
);

echo $outPut->read();
