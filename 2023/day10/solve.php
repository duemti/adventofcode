<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 10: Pipe Maze.
 *
 * Part 1 example answer is 80. Part 2 example answer is 10.
 */

/** @var array<string, array{0: int, 1: int}> */
const DELTAS = [
    'N' => [-1, 0],
    'S' => [1, 0],
    'W' => [0, -1],
    'E' => [0, 1],
];

/** @var array<string, string> */
const OPPOSITE = [
    'N' => 'S',
    'S' => 'N',
    'W' => 'E',
    'E' => 'W',
];

/** @var array<string, array{0: string, 1: string}> */
const PIPES = [
    '|' => ['N', 'S'],
    '-' => ['E', 'W'],
    'L' => ['N', 'E'],
    'J' => ['N', 'W'],
    '7' => ['S', 'W'],
    'F' => ['S', 'E'],
];

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);

echo 'Advent of Code 2023 — Day 10: Pipe Maze', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($grid), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$farthest = farthestDistance($grid);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d steps      %8s\n", 'Part 1  farthest', $farthest, formatDuration($elapsed));

$startedAt = hrtime(true);
$enclosed = enclosedTiles($grid);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d tiles      %8s\n", 'Part 2  enclosed', $enclosed, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $grid
 */
function farthestDistance(array $grid): int
{
    return intdiv(count(traceLoop($grid)), 2);
}

/**
 * Interior tiles from the shoelace area and Pick's theorem:
 * interior = area - boundary/2 + 1.
 *
 * @param  list<string>  $grid
 */
function enclosedTiles(array $grid): int
{
    $loop = traceLoop($grid);
    $boundary = count($loop);
    $doubleArea = 0;

    for ($i = 0; $i < $boundary; $i++) {
        [$y1, $x1] = $loop[$i];
        [$y2, $x2] = $loop[($i + 1) % $boundary];
        $doubleArea += $x1 * $y2 - $x2 * $y1;
    }

    $doubleArea = abs($doubleArea);

    if ($doubleArea % 2 !== 0) {
        throw new RuntimeException('Shoelace area is not an even integer.');
    }

    return intdiv($doubleArea - $boundary, 2) + 1;
}

/**
 * Ordered loop cells, starting at S. S is replaced by the pipe shape
 * implied by the two neighbors that open back toward it.
 *
 * @param  list<string>  $grid
 * @return list<array{0: int, 1: int}>
 */
function traceLoop(array $grid): array
{
    [$grid, $startY, $startX] = replaceStart($grid);
    $direction = PIPES[$grid[$startY][$startX]][0];
    $y = $startY;
    $x = $startX;
    $loop = [];
    $limit = count($grid) * strlen($grid[0]);

    do {
        if (count($loop) > $limit) {
            throw new RuntimeException('Pipe walk did not close');
        }

        $loop[] = [$y, $x];
        [$dy, $dx] = DELTAS[$direction];
        $y += $dy;
        $x += $dx;
        $arrivedFrom = OPPOSITE[$direction];
        $openings = PIPES[$grid[$y][$x]];
        $direction = $openings[0] === $arrivedFrom ? $openings[1] : $openings[0];
    } while ($y !== $startY || $x !== $startX);

    return $loop;
}

/**
 * @param  list<string>  $grid
 * @return array{0: list<string>, 1: int, 2: int}
 */
function replaceStart(array $grid): array
{
    [$startY, $startX] = findStart($grid);
    $directions = [];

    $height = count($grid);

    foreach (DELTAS as $direction => [$dy, $dx]) {
        $y = $startY + $dy;
        $x = $startX + $dx;

        if ($y < 0 || $y >= $height || $x < 0 || $x >= strlen($grid[$y])) {
            continue;
        }

        $tile = $grid[$y][$x];

        if (! isset(PIPES[$tile])) {
            continue;
        }

        if (in_array(OPPOSITE[$direction], PIPES[$tile], true)) {
            $directions[] = $direction;
        }
    }

    if (count($directions) !== 2) {
        throw new RuntimeException('Start tile does not connect to exactly two pipes.');
    }

    sort($directions);
    $grid[$startY][$startX] = match ($directions) {
        ['N', 'S'] => '|',
        ['E', 'W'] => '-',
        ['E', 'N'] => 'L',
        ['N', 'W'] => 'J',
        ['S', 'W'] => '7',
        ['E', 'S'] => 'F',
        default => throw new RuntimeException('Start tile has no pipe shape.'),
    };

    return [$grid, $startY, $startX];
}

/**
 * @param  list<string>  $grid
 * @return array{0: int, 1: int}
 */
function findStart(array $grid): array
{
    foreach ($grid as $y => $row) {
        $x = strpos($row, 'S');

        if ($x !== false) {
            return [$y, $x];
        }
    }

    throw new RuntimeException('Map is missing the start tile S.');
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
