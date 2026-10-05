<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 9: Mirage Maintenance
 *
 * Part 1 example answer is 114. Part 2 example answer is 2.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 9: Mirage Maintenance', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$nextSum = sumExtrapolations($lines, false);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d value %8s\n", 'Part 1  next', $nextSum, formatDuration($elapsed));

$startedAt = hrtime(true);
$previousSum = sumExtrapolations($lines, true);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d value %8s\n", 'Part 2  previous', $previousSum, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $lines
 */
function sumExtrapolations(array $lines, bool $previous): int
{
    $total = 0;

    foreach ($lines as $line) {
        $history = array_map(intval(...), preg_split('/\s+/', trim($line)) ?: []);
        $total += $previous ? previousValue($history) : nextValue($history);
    }

    return $total;
}

/**
 * Next value is the sum of the last number on every difference row,
 * including the original history.
 *
 * @param  list<int>  $history
 */
function nextValue(array $history): int
{
    $total = 0;

    foreach (differenceRows($history) as $row) {
        $total += $row[array_key_last($row)];
    }

    return $total;
}

/**
 * Previous value walks from the zero row upward:
 * newFirst = first - belowFirst.
 *
 * @param  list<int>  $history
 */
function previousValue(array $history): int
{
    $below = 0;

    foreach (array_reverse(differenceRows($history)) as $row) {
        $below = $row[0] - $below;
    }

    return $below;
}

/**
 * @param  list<int>  $history
 * @return list<list<int>>
 */
function differenceRows(array $history): array
{
    $rows = [$history];
    $current = $history;

    while (! isZeroRow($current)) {
        $differences = [];
        $count = count($current);

        for ($index = 1; $index < $count; $index++) {
            $differences[] = $current[$index] - $current[$index - 1];
        }

        $rows[] = $differences;
        $current = $differences;
    }

    return $rows;
}

/**
 * @param  list<int>  $row
 */
function isZeroRow(array $row): bool
{
    foreach ($row as $value) {
        if ($value !== 0) {
            return false;
        }
    }

    return $row !== [];
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
