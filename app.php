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

/**
 * @param DateRepository $dateRepository
 * @param DateTime $dateTime
 * @return Task[] $input
 */
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
    CommandFinder    $commandFinder,
    string           $input
): void {
    if (empty($input)) {
        $output->writeNl("-help para más información");
        return;
    }

    if (isHelperCommand($input)) {
        $output->writeNl("Lista de comandos:");
        $output->addEOL();
        $commandFinder->help($output);
        return;
    }

    if (isNotCommandParameter($input)) {
        saveTask($dateRepository, $dateTime, $input, $output);

        return;
    }

    $command = $commandFinder->findCommand($input);
    $command?->__invoke();
}

function isHelperCommand(string $input): bool
{
    return str_starts_with($input, '-help');
}

function saveTask(DateRepository $dateRepository, DateTime $dateTime, string $input, OutPutOInterface $output): void
{
    $dateRepository->save($dateTime, $input);
    $output->write("[{$dateTime->__toString()}]: {$input}");
    $output->addEOL();
    $output->addEOL();
    $output->write("Tarea registrada !");
    $output->addEOL();
}

function isNotCommandParameter(string $input): bool
{
    return !str_starts_with($input, '-');
}

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

showTasks(
    $time,
    $dateRepository,
    $outPut,
    $commandFinder,
    $input
);

echo $outPut->read();
