<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 16: The Floor Will Be Lava.
 *
 * Part 1 example answer is 46. Part 2 example answer is 51.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);

echo 'Advent of Code 2023 — Day 16: The Floor Will Be Lava', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($grid), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$energized = countEnergized($grid, 0, 0, 1);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d tiles    %8s\n", 'Part 1  east beam', $energized, formatDuration($elapsed));

$startedAt = hrtime(true);
$best = maxEnergized($grid);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d tiles    %8s\n", 'Part 2  best edge', $best, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Directions are 0 north, 1 east, 2 south, 3 west.
 *
 * @param  list<string>  $grid
 */
function countEnergized(array $grid, int $startRow, int $startCol, int $startDir): int
{
    $rows = count($grid);
    $cols = strlen($grid[0]);
    $rowStep = [-1, 0, 1, 0];
    $colStep = [0, 1, 0, -1];

    /** @var array<int, true> $seen */
    $seen = [];
    /** @var array<int, true> $energized */
    $energized = [];
    /** @var list<array{0: int, 1: int, 2: int}> $queue */
    $queue = [[$startRow, $startCol, $startDir]];
    $head = 0;

    while ($head < count($queue)) {
        [$row, $col, $direction] = $queue[$head];
        $head++;

        $state = (($row * $cols) + $col) * 4 + $direction;

        if (isset($seen[$state])) {
            continue;
        }

        $seen[$state] = true;
        $energized[($row * $cols) + $col] = true;

        foreach (nextDirections($grid[$row][$col], $direction) as $nextDirection) {
            $nextRow = $row + $rowStep[$nextDirection];
            $nextCol = $col + $colStep[$nextDirection];

            if ($nextRow < 0 || $nextRow >= $rows || $nextCol < 0 || $nextCol >= $cols) {
                continue;
            }

            $queue[] = [$nextRow, $nextCol, $nextDirection];
        }
    }

    return count($energized);
}

/**
 * Try every perimeter tile heading inward. Corners are entered twice, once
 * for each edge, so both inward headings are covered.
 *
 * @param  list<string>  $grid
 */
function maxEnergized(array $grid): int
{
    $rows = count($grid);
    $cols = strlen($grid[0]);
    $best = 0;

    for ($col = 0; $col < $cols; $col++) {
        $best = max($best, countEnergized($grid, 0, $col, 2));
        $best = max($best, countEnergized($grid, $rows - 1, $col, 0));
    }

    for ($row = 0; $row < $rows; $row++) {
        $best = max($best, countEnergized($grid, $row, 0, 1));
        $best = max($best, countEnergized($grid, $row, $cols - 1, 3));
    }

    return $best;
}

/**
 * Empty space continues. Mirrors reflect. A splitter that the beam hits
 * point-first passes through; a splitter hit on its side sends two beams.
 *
 * @return list<int>
 */
function nextDirections(string $tile, int $direction): array
{
    if ($tile === '/') {
        return [[1, 0, 3, 2][$direction]];
    }

    if ($tile === '\\') {
        return [[3, 2, 1, 0][$direction]];
    }

    if ($tile === '|' && ($direction === 1 || $direction === 3)) {
        return [0, 2];
    }

    if ($tile === '-' && ($direction === 0 || $direction === 2)) {
        return [1, 3];
    }

    return [$direction];
}

/**
 * @return list<string>
 */
function loadGrid(string $path): array
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
