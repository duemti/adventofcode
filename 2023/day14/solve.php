<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 14: Parabolic Reflector Dish.
 *
 * Part 1 example answer is 136. Part 2 example answer is 64.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadLines($path);

echo 'Advent of Code 2023 — Day 14: Parabolic Reflector Dish', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($grid), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$northLoad = totalLoad(tilt($grid, 'N'));
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d load     %8s\n", 'Part 1  north tilt', $northLoad, formatDuration($elapsed));

$startedAt = hrtime(true);
$spunLoad = loadAfterSpins($grid, 1_000_000_000);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d load     %8s\n", 'Part 2  spin cycle', $spunLoad, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Slide every round rock as far as it will go in one direction.
 *
 * Rocks move until they hit a cube, another round rock, or the edge.
 * Scanning from the edge they move toward means a rock that has already
 * settled blocks the ones behind it.
 *
 * @param  list<string>  $grid
 * @return list<string>
 */
function tilt(array $grid, string $direction): array
{
    $rows = count($grid);
    $cols = strlen($grid[0]);

    if ($direction === 'N' || $direction === 'S') {
        $north = $direction === 'N';

        for ($c = 0; $c < $cols; $c++) {
            $dest = $north ? 0 : $rows - 1;

            for ($step = 0; $step < $rows; $step++) {
                $r = $north ? $step : $rows - 1 - $step;
                $cell = $grid[$r][$c];

                if ($cell === '#') {
                    $dest = $north ? $r + 1 : $r - 1;
                } elseif ($cell === 'O') {
                    if ($dest !== $r) {
                        $grid[$dest][$c] = 'O';
                        $grid[$r][$c] = '.';
                    }

                    $dest += $north ? 1 : -1;
                }
            }
        }

        return $grid;
    }

    $west = $direction === 'W';

    for ($r = 0; $r < $rows; $r++) {
        $dest = $west ? 0 : $cols - 1;

        for ($step = 0; $step < $cols; $step++) {
            $c = $west ? $step : $cols - 1 - $step;
            $cell = $grid[$r][$c];

            if ($cell === '#') {
                $dest = $west ? $c + 1 : $c - 1;
            } elseif ($cell === 'O') {
                if ($dest !== $c) {
                    $grid[$r][$dest] = 'O';
                    $grid[$r][$c] = '.';
                }

                $dest += $west ? 1 : -1;
            }
        }
    }

    return $grid;
}

/**
 * One spin cycle: north, then west, then south, then east.
 *
 * @param  list<string>  $grid
 * @return list<string>
 */
function spin(array $grid): array
{
    foreach (['N', 'W', 'S', 'E'] as $direction) {
        $grid = tilt($grid, $direction);
    }

    return $grid;
}

/**
 * Load on the platform after exactly $spins full cycles.
 *
 * The dish returns to a previous arrangement. The first repeated grid
 * marks the start of a loop, so the state after a billion cycles is the
 * same as the state a short offset into that loop.
 *
 * @param  list<string>  $grid
 */
function loadAfterSpins(array $grid, int $spins): int
{
    /** @var array<string, int> $seen */
    $seen = [];
    /** @var array<int, int> $loads */
    $loads = [];

    $guard = count($grid) * strlen($grid[0]);

    for ($i = 0; $i < $spins; $i++) {
        if ($i > $guard) {
            throw new RuntimeException('Spin cycle did not repeat');
        }

        $key = implode("\n", $grid);

        if (isset($seen[$key])) {
            $start = $seen[$key];
            $length = $i - $start;
            $index = $start + ($spins - $start) % $length;

            return $loads[$index];
        }

        $seen[$key] = $i;
        $loads[$i] = totalLoad($grid);
        $grid = spin($grid);
    }

    return totalLoad($grid);
}

/**
 * Total load: each round rock scores (rows - row), with row 0 at the top.
 *
 * @param  list<string>  $grid
 */
function totalLoad(array $grid): int
{
    $rows = count($grid);
    $load = 0;

    foreach ($grid as $row => $line) {
        $load += substr_count($line, 'O') * ($rows - $row);
    }

    return $load;
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
