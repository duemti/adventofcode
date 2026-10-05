<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 21: Step Counter.
 *
 * Example part 1 (6 steps) is 16. Real part 1 is exactly 64 steps.
 * Example part 2 at 10 steps is 50. Real part 2 is exactly 26501365 steps
 * on an infinite tiled grid and uses the quadratic.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);
[$startRow, $startCol] = findStart($grid);
$rows = count($grid);
$example = $rows < 20;

echo 'Advent of Code 2023 — Day 21: Step Counter', PHP_EOL;
echo 'Input: ', basename($path), '   ', $rows, ' rows', PHP_EOL, PHP_EOL;

$part1Steps = $example ? 6 : 64;
$startedAt = hrtime(true);
$part1 = countReachable($grid, $startRow, $startCol, $part1Steps, false);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %10d plots   %8s\n", "Part 1  {$part1Steps} steps", $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
if ($example) {
    $part2 = countReachable($grid, $startRow, $startCol, 10, true);
    $part2Label = 'Part 2  10 steps';
} else {
    $part2 = quadraticReachable($grid, $startRow, $startCol);
    $part2Label = 'Part 2  quadratic';
}
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %10d plots   %8s\n", $part2Label, $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Garden plots reachable in exactly $steps moves.
 *
 * The first visit is the shortest path. A plot at distance d is reachable
 * in exactly $steps when d <= $steps and d has the same parity, because two
 * steps can be wasted by walking back and forth. Rocks block movement.
 * The finite grid does not wrap. The infinite grid repeats the map, and
 * coordinates may be negative.
 *
 * Infinite searches refuse anything above 400 steps. The loop itself stops
 * at $steps, so it never walks the full 26501365-step distance.
 *
 * @param  list<string>  $grid
 */
function countReachable(array $grid, int $startRow, int $startCol, int $steps, bool $infinite): int
{
    if ($infinite && $steps > 400) {
        throw new InvalidArgumentException('Infinite BFS refuses more than 400 steps');
    }

    $height = count($grid);
    $width = strlen($grid[0]);
    $directions = [[-1, 0], [1, 0], [0, -1], [0, 1]];

    /** @var array<string, int> $distance */
    $distance = [$startRow.','.$startCol => 0];
    /** @var list<array{0: int, 1: int}> $frontier */
    $frontier = [[$startRow, $startCol]];

    for ($depth = 0; $depth < $steps; $depth++) {
        $next = [];

        foreach ($frontier as [$row, $col]) {
            foreach ($directions as [$dRow, $dCol]) {
                $nextRow = $row + $dRow;
                $nextCol = $col + $dCol;

                if ($infinite) {
                    $tile = $grid[positiveMod($nextRow, $height)][positiveMod($nextCol, $width)];
                } else {
                    if ($nextRow < 0 || $nextRow >= $height || $nextCol < 0 || $nextCol >= $width) {
                        continue;
                    }
                    $tile = $grid[$nextRow][$nextCol];
                }

                if ($tile === '#') {
                    continue;
                }

                $key = $nextRow.','.$nextCol;
                if (isset($distance[$key])) {
                    continue;
                }

                $distance[$key] = $depth + 1;
                $next[] = [$nextRow, $nextCol];
            }
        }

        $frontier = $next;
    }

    $parity = $steps % 2;
    $reachable = 0;

    foreach ($distance as $dist) {
        if (($dist % 2) === $parity) {
            $reachable++;
        }
    }

    return $reachable;
}

/**
 * Reachable plots after exactly 26501365 steps on the infinite tiling.
 *
 * The real map is 131 by 131 with the start in the center, and that row and
 * column are open garden. 26501365 = 202300 * 131 + 65, so the counts at
 * 65, 196, and 327 steps sit on a quadratic. With n = 202300:
 *
 *   s0 + n * (s1 - s0) + n * (n - 1) / 2 * ((s2 - s1) - (s1 - s0))
 *
 * @param  list<string>  $grid
 */
function quadraticReachable(array $grid, int $startRow, int $startCol): int
{
    $size = count($grid);
    $half = intdiv($size - 1, 2);
    $target = 26_501_365;
    $n = intdiv($target - $half, $size);

    $s0 = countReachable($grid, $startRow, $startCol, $half, true);
    $s1 = countReachable($grid, $startRow, $startCol, $half + $size, true);
    $s2 = countReachable($grid, $startRow, $startCol, $half + 2 * $size, true);

    $first = $s1 - $s0;
    $second = ($s2 - $s1) - $first;

    return $s0 + $n * $first + intdiv($n * ($n - 1), 2) * $second;
}

/**
 * @return list<string>
 */
function loadGrid(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false || $lines === []) {
        throw new RuntimeException("Unable to read {$path}");
    }

    return array_values($lines);
}

/**
 * @param  list<string>  $grid
 * @return array{0: int, 1: int}
 */
function findStart(array $grid): array
{
    foreach ($grid as $row => $line) {
        $col = strpos($line, 'S');
        if ($col !== false) {
            return [$row, $col];
        }
    }

    throw new RuntimeException('Start S not found');
}

function positiveMod(int $value, int $modulus): int
{
    $remainder = $value % $modulus;

    return $remainder < 0 ? $remainder + $modulus : $remainder;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
