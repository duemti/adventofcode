<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 11: Cosmic Expansion.
 *
 * Part 1 example answer is 374 (expansion factor 2).
 * Part 2 on the real problem uses expansion factor 1000000.
 * Example checks: expansion 10 sums to 1030, expansion 100 sums to 8410.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 11: Cosmic Expansion', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$part1 = sumDistances($lines, 2);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-28s %12d sum %8s\n", 'Part 1  expansion 2', $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
$part2 = sumDistances($lines, 1_000_000);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-28s %12d sum %8s\n", 'Part 2  expansion 1000000', $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Sum pairwise distances after empty rows and columns expand by $factor.
 *
 * Distance is Manhattan. Each empty row or column strictly between a pair
 * adds (factor - 1) extra steps. The sum fits in a signed 64-bit integer.
 *
 * @param  list<string>  $lines
 */
function sumDistances(array $lines, int $factor): int
{
    [$galaxies, $rowPrefix, $colPrefix] = locateGalaxies($lines);
    $extra = $factor - 1;
    $expanded = [];

    foreach ($galaxies as [$row, $col]) {
        $expanded[] = [
            $row + $extra * $rowPrefix[$row],
            $col + $extra * $colPrefix[$col],
        ];
    }

    $sum = 0;
    $count = count($expanded);

    for ($i = 0; $i < $count; $i++) {
        [$rowA, $colA] = $expanded[$i];

        for ($j = $i + 1; $j < $count; $j++) {
            [$rowB, $colB] = $expanded[$j];
            $sum += abs($rowB - $rowA) + abs($colB - $colA);
        }
    }

    return $sum;
}

/**
 * Galaxies as (row, col), plus a prefix of how many empty rows or columns
 * sit strictly before each index.
 *
 * @param  list<string>  $lines
 * @return array{0: list<array{0: int, 1: int}>, 1: array<int, int>, 2: array<int, int>}
 */
function locateGalaxies(array $lines): array
{
    $height = count($lines);
    $width = 0;

    foreach ($lines as $line) {
        $width = max($width, strlen($line));
    }

    $galaxies = [];
    $rowHasGalaxy = array_fill(0, $height, false);
    $colHasGalaxy = array_fill(0, $width, false);

    foreach ($lines as $row => $line) {
        $length = strlen($line);

        for ($col = 0; $col < $length; $col++) {
            if ($line[$col] !== '#') {
                continue;
            }

            $galaxies[] = [$row, $col];
            $rowHasGalaxy[$row] = true;
            $colHasGalaxy[$col] = true;
        }
    }

    return [$galaxies, emptyPrefix($rowHasGalaxy), emptyPrefix($colHasGalaxy)];
}

/**
 * For each index, how many empty positions are strictly before it.
 *
 * A galaxy never sits on an empty row or column, so the prefix difference
 * between two galaxies is the number of empty lines strictly between them.
 *
 * @param  array<int, bool>  $occupied
 * @return array<int, int>
 */
function emptyPrefix(array $occupied): array
{
    $prefix = [];
    $emptyBefore = 0;

    foreach ($occupied as $index => $hasGalaxy) {
        $prefix[$index] = $emptyBefore;

        if (!$hasGalaxy) {
            $emptyBefore++;
        }
    }

    return $prefix;
}

/**
 * @return list<string>
 */
function loadLines(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);

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
