<?php

declare(strict_types=1);

/**
 * Advent of Code 2024, Day 1: Historian Hysteria.
 *
 * Paste the example into example.txt and the puzzle input into puzzle-input.txt.
 * Run: php solve.php [path]
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2024 — Day 1: Historian Hysteria', PHP_EOL;
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
    $left = [];
    $right = [];

    foreach ($lines as $line) {
        preg_match('/(\d+)\s+(\d+)/', $line, $matches);
        $left[] = (int) $matches[1];
        $right[] = (int) $matches[2];
    }

    sort($left);
    sort($right);

    $distances = array_map(function ($left, $right) {
        return abs($left - $right);
    }, $left, $right);

    return array_sum($distances);
}

/**
 * @param  list<string>  $lines
 */
function partTwo(array $lines): int
{
    $left = [];
    $right = [];

    foreach ($lines as $line) {
        preg_match('/(\d+)\s+(\d+)/', $line, $matches);
        $left[] = (int) $matches[1];
        $right[] = (int) $matches[2];
    }

    $countsLeft = array_count_values($right);
    $countsRight = array_count_values($left);
    $score = 0;

    foreach ($countsLeft as $left => $count) {
        $score += $count * $left * ($countsRight[$left] ?? 0);
    }

    return $score;
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
