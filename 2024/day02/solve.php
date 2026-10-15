<?php

declare(strict_types=1);

/**
 * Advent of Code 2024, Day 2: Red-Nosed Reports.
 *
 * Paste the example into example.txt and the puzzle input into puzzle-input.txt.
 * Run: php solve.php [path]
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2024 — Day 2: Red-Nosed Reports', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$partOne = partOne($lines);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d            %8s\n", 'Part 1', $partOne, formatDuration($elapsed));

$startedAt = hrtime(true);
$partTwo = partTwo($lines);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d            %8s\n", 'Part 2', $partTwo, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $lines
 */
function partOne(array $lines): int
{
    $scores = array_map(function ($line) {
        $levels = explode(' ', $line);

        return isSafe($levels);
        
    }, $lines);

    return array_sum($scores);
}

/**
 * @param  list<string>  $levels
 */
function isSafe(array $levels): int
{
    $direction = '';

    foreach ($levels as $index => $level) {
        if ($index === 0) {
            continue;
        }

        $levelDiff = (int) $level - (int) $levels[$index - 1];

        if ($direction === '') {
            $direction = $levelDiff >= 1 ? 'increase' : 'decrease';
        }

        if ($direction === 'increase' && $levelDiff >= 1 && $levelDiff <= 3) {
            continue;
        }

        if ($direction === 'decrease' && $levelDiff <= -1 && $levelDiff >= -3) {
            continue;
        }

        return 0;
    }

    return 1;
}
/**
 * @param  list<string>  $lines
 */
function partTwo(array $lines): int
{
    $scores = array_map(function ($line) {
        $levels = explode(' ', $line);

        foreach ($levels as $index => $level) {
            $without = $levels;
            unset($without[$index]);

            if (isSafe(array_values($without)) === 1) {
                return 1;
            }
        }

        return 0;
    }, $lines);

    return array_sum($scores);
}

/**
 * @return list<string>
 */
function loadLines(string $path): array
{
    if (! is_file($path)) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $contents = str_replace("\r\n", "\n", $contents);
    $contents = rtrim($contents, "\n");

    if ($contents === '') {
        return [];
    }

    return explode("\n", $contents);
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
