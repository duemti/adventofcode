<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 6: Wait For It
 *
 * Part 1 example answer is 288. Part 2 example answer is 71503.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 6: Wait For It', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$product = waysProduct(parseRaces($lines));
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d ways %8s\n", 'Part 1  columns', $product, formatDuration($elapsed));

$startedAt = hrtime(true);
$single = waysToWin(...parseKernedRace($lines));
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d ways %8s\n", 'Part 2  one race', $single, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<array{0: int, 1: int}>  $races
 */
function waysProduct(array $races): int
{
    $product = 1;

    foreach ($races as [$time, $record]) {
        $product *= waysToWin($time, $record);
    }

    return $product;
}

/**
 * Holding the button for h milliseconds travels (time - h) * h.
 * Winning holds are the integers strictly between the roots of
 * h^2 - time*h + record = 0. Roots come from the quadratic formula;
 * the two boundary integers are then checked exactly.
 */
function waysToWin(int $time, int $record): int
{
    if ($time <= 1 || $record < 0) {
        return 0;
    }

    $discriminant = (float) $time * (float) $time - 4.0 * (float) $record;

    if ($discriminant <= 0.0) {
        return 0;
    }

    $root = sqrt($discriminant);
    $first = (int) floor(((float) $time - $root) / 2.0) + 1;
    $last = (int) ceil(((float) $time + $root) / 2.0) - 1;

    $first = lowestWinningHold($time, $record, $first);
    $last = highestWinningHold($time, $record, $last);

    if ($first > $last || ! beatsRecord($time, $record, $first)) {
        return 0;
    }

    return $last - $first + 1;
}

function lowestWinningHold(int $time, int $record, int $hold): int
{
    if (beatsRecord($time, $record, $hold)) {
        while ($hold > 1 && beatsRecord($time, $record, $hold - 1)) {
            $hold--;
        }

        return $hold;
    }

    while ($hold < $time - 1 && ! beatsRecord($time, $record, $hold)) {
        $hold++;
    }

    return $hold;
}

function highestWinningHold(int $time, int $record, int $hold): int
{
    if (beatsRecord($time, $record, $hold)) {
        while ($hold + 1 < $time && beatsRecord($time, $record, $hold + 1)) {
            $hold++;
        }

        return $hold;
    }

    while ($hold > 1 && ! beatsRecord($time, $record, $hold)) {
        $hold--;
    }

    return $hold;
}

/**
 * (time - hold) * hold > record, without multiplying into an overflowed int.
 */
function beatsRecord(int $time, int $record, int $hold): bool
{
    if ($hold <= 0 || $hold >= $time) {
        return false;
    }

    return ($time - $hold) > intdiv($record, $hold);
}

/**
 * @param  list<string>  $lines
 * @return list<array{0: int, 1: int}>
 */
function parseRaces(array $lines): array
{
    $times = integers($lines[0]);
    $records = integers($lines[1]);
    $races = [];

    foreach ($times as $index => $time) {
        $races[] = [$time, $records[$index]];
    }

    return $races;
}

/**
 * @param  list<string>  $lines
 * @return array{0: int, 1: int}
 */
function parseKernedRace(array $lines): array
{
    return [joinedInteger($lines[0]), joinedInteger($lines[1])];
}

/**
 * @return list<int>
 */
function integers(string $line): array
{
    preg_match_all('/\d+/', $line, $matches);

    return array_map(intval(...), $matches[0]);
}

function joinedInteger(string $line): int
{
    return (int) preg_replace('/\D+/', '', $line);
}

/**
 * @return list<string>
 */
function loadLines(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    return $lines;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
