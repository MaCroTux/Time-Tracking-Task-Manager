<?php

// ---- INTERFACE ----
interface DateRepository
{
    public function save(DomainDateTime $dateTime, string $dateTracking);
    public function readAll(DomainDateTime $now): array;
}
interface OutPutOInterface
{
    public function write(string $message): void;
    public function addEOL(): void;
    public function read(): string;
}
// ---- CLASS ----
class DomainDateTime
{
    private const DATE_FORMAT = 'l H:m:s';
    private const TIME_ZONE = 'Europe/Madrid';
    private DateTimeInterface $dateTimeImmutable;

    private function __construct(
        DateTimeInterface $dateTimeImmutable
    ) {
        $this->dateTimeImmutable = $dateTimeImmutable;
    }

    public static function fromString(string $date): self
    {
        return new self(
            DateTimeImmutable::createFromFormat(
                DateTime::ATOM,
                $date,
                new DateTimeZone(DomainDateTime::TIME_ZONE)
            )
        );
    }

    public function diff(DomainDateTime $dateTime): DateInterval
    {
        return $this->dateTimeImmutable->diff($dateTime->dateTimeImmutable);
    }

    public static function now(): self
    {
        return new self(
            new DateTimeImmutable(
                "now",
                new DateTimeZone(DomainDateTime::TIME_ZONE)
            )
        );
    }

    public function format(): string
    {
        return $this->dateTimeImmutable->format(DateTimeImmutable::ATOM);
    }

    public function __toString(): string
    {
        return $this->dateTimeImmutable->format(DomainDateTime::DATE_FORMAT);
    }
}
class JsonDateRepository implements DateRepository
{
    private const FILENAME = './dates.json';

    public function readAll(?DomainDateTime $now = null): array
    {
        if (!file_exists(JsonDateRepository::FILENAME)) {
            return [];
        }

        if ($now !== null) {
            $all = jsonDecodeFile(JsonDateRepository::FILENAME);

            return array_filter(
                $all,
                function ($date) use ($now) {
                    $dateTime = DateTimeImmutable::createFromFormat(
                        DateTime::ATOM,
                        $date['date']
                    );
                    $now = DateTimeImmutable::createFromFormat(
                        DateTime::ATOM,
                        $now->format()
                    );

                    return $dateTime->diff($now)->d === 0;
                }
            );
        }

        return jsonDecodeFile(JsonDateRepository::FILENAME);
    }

    public function save(DomainDateTime $dateTime, string $dateTracking): void
    {
        $data = [];
        if (file_exists(JsonDateRepository::FILENAME)) {
            $data = json_decode(
                file_get_contents(JsonDateRepository::FILENAME),
                true
            );
        }

        $data[$dateTime->format()] = [
            'dateWeek' => $dateTime->__toString(),
            'date' => $dateTime->format(),
            'tracking' => $dateTracking,
        ];
        file_put_contents(JsonDateRepository::FILENAME, json_encode($data));
    }
}
class ConsoleOutput implements OutPutOInterface
{
    private string $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function write(string $message): void
    {
        $this->message .= $message;
    }
    public function addEOL(): void
    {
        $this->message .= PHP_EOL;
    }

    public function read(): string
    {
        return $this->message;
    }
}
// ---- FUNCTION ----
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
function getAccumulateTimeFromPrevTask(string $prevDate, string $date): string
{
    $timeAccumulated = "";

    if (empty($prevDate)) {
        return "";
    }

    $datePrev = DomainDateTime::fromString($prevDate);
    $dateNow = DomainDateTime::fromString($date);
    $dateAccumulated = $datePrev->diff($dateNow);

    if ($dateAccumulated->h > 0) {
        $timeAccumulated .= "{$dateAccumulated->h}h ";
    }
    if ($dateAccumulated->i > 0) {
        $timeAccumulated .= "{$dateAccumulated->i}m";
    }

    return "+$timeAccumulated";
}
function getTaskAndTimeFromString($prevDate, $date, $task): string
{
    if ($prevDate !== null) {
        $timeAccumulated = getAccumulateTimeFromPrevTask($prevDate, $date);
        return "[{$task['dateWeek']}] {$task['tracking']} ({$timeAccumulated})\n";
    }

    return "[{$task['dateWeek']}] {$task['tracking']}\n";
}
function getList(DateRepository $dateRepository, DomainDateTime $dateTime): array
{
    $dates = $dateRepository->readAll($dateTime);

    $list = [];
    $prevDate = null;
    foreach ($dates as $date => $task) {
        $list[] = getTaskAndTimeFromString($prevDate, $date, $task);
        $prevDate = $date;
    }

    return $list;
}
function printList(
    DateRepository $dateRepository,
    DomainDateTime $dateTime,
    OutPutOInterface $outPut,
): void {
    $outPut->addEOL();
    foreach (getList($dateRepository, $dateTime) as $item) {
        $outPut->write($item);
    }
    $all = $dateRepository->readAll($dateTime);
    $dates = array_keys($all);
    $lastDate = getAccumulateTimeFromPrevTask(end($dates), date(DATE_ATOM));

    if (!empty($lastDate)) {
        $outPut->write(PHP_EOL . "Llevas actualmente: {$lastDate}, en tu ultima tarea." . PHP_EOL);
    }
}
function showTasks(DomainDateTime $dateTime, DateRepository $dateRepository, ConsoleOutput $output): void
{
    echo "[{$dateTime->__toString()}]: ";
    $input = trim(read());

    if (empty($input)) {
        printList($dateRepository, $dateTime, $output);

        return;
    }

    $dateRepository->save($dateTime, $input);
}

// ---- MAIN ----
$outPut = new ConsoleOutput("");

showTasks(
    DomainDateTime::now(),
    new JsonDateRepository(),
    $outPut,
);

echo $outPut->read();
