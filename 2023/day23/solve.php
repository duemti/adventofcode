<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 23: A Long Walk.
 *
 * The maze is contracted to a graph of junctions. Part 1 keeps slopes as
 * one-way edges. Part 2 treats every slope as a normal path.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);

echo 'Advent of Code 2023 — Day 23: A Long Walk', PHP_EOL;
echo 'Input: ', basename($path), PHP_EOL, PHP_EOL;

foreach ([
    'Part 1  icy slopes' => true,
    'Part 2  dry slopes' => false,
] as $label => $respectSlopes) {
    $startedAt = hrtime(true);
    $steps = longestHike($grid, $respectSlopes);
    $elapsed = (hrtime(true) - $startedAt) / 1_000_000;

    printf("  %-18s %6d steps   %8s\n", $label, $steps, formatDuration($elapsed));
}

echo PHP_EOL;

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}

/**
 * @return list<list<string>>
 */
function loadGrid(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    return array_map(str_split(...), $lines);
}

/**
 * @param  list<list<string>>  $grid
 */
function longestHike(array $grid, bool $respectSlopes): int
{
    $rows = count($grid);
    $cols = count($grid[0]);
    $startColumn = array_search('.', $grid[0], true);
    $endColumn = array_search('.', $grid[$rows - 1], true);

    if ($startColumn === false || $endColumn === false) {
        throw new RuntimeException('Map is missing a start or end tile.');
    }

    $start = [0, $startColumn];
    $end = [$rows - 1, $endColumn];
    $graph = contractTrails($grid, $start, $end, $respectSlopes);

    $max = 0;
    $seen = [];
    search($graph, keyOf($start), keyOf($end), 0, $seen, $max);

    return $max;
}

/**
 * @param  list<list<string>>  $grid
 * @param  array{0: int, 1: int}  $start
 * @param  array{0: int, 1: int}  $end
 * @return array<string, array<string, int>>
 */
function contractTrails(array $grid, array $start, array $end, bool $respectSlopes): array
{
    $pois = [];

    foreach ([$start, $end] as $point) {
        $pois[keyOf($point)] = $point;
    }

    foreach ($grid as $row => $cells) {
        foreach ($cells as $column => $tile) {
            if ($tile === '#') {
                continue;
            }

            if (count(openNeighbors($grid, $row, $column)) > 2) {
                $pois["{$row},{$column}"] = [$row, $column];
            }
        }
    }

    $graph = [];

    foreach ($pois as $fromKey => [$row, $column]) {
        foreach (legalMoves($grid, $row, $column, $respectSlopes) as [$nextRow, $nextColumn]) {
            $edge = followTrail($grid, $pois, $row, $column, $nextRow, $nextColumn, $respectSlopes);

            if ($edge === null) {
                continue;
            }

            [$toKey, $distance] = $edge;
            $graph[$fromKey][$toKey] = max($graph[$fromKey][$toKey] ?? 0, $distance);
        }
    }

    return $graph;
}

/**
 * Walk from a junction along one trail until the next junction.
 *
 * @param  list<list<string>>  $grid
 * @param  array<string, array{0: int, 1: int}>  $pois
 * @return array{0: string, 1: int}|null
 */
function followTrail(array $grid, array $pois, int $fromRow, int $fromColumn, int $row, int $column, bool $respectSlopes): ?array
{
    $distance = 1;
    $previousRow = $fromRow;
    $previousColumn = $fromColumn;
    $seen = [keyOf([$fromRow, $fromColumn]) => true];

    while (! isset($pois["{$row},{$column}"])) {
        $here = "{$row},{$column}";

        if (isset($seen[$here])) {
            return null;
        }

        $seen[$here] = true;
        $next = null;

        foreach (legalMoves($grid, $row, $column, $respectSlopes) as [$nextRow, $nextColumn]) {
            if ($nextRow === $previousRow && $nextColumn === $previousColumn) {
                continue;
            }

            if ($next !== null) {
                return null;
            }

            $next = [$nextRow, $nextColumn];
        }

        if ($next === null) {
            return null;
        }

        [$previousRow, $previousColumn] = [$row, $column];
        [$row, $column] = $next;
        $distance++;
    }

    return ["{$row},{$column}", $distance];
}

/**
 * @param  array<string, array<string, int>>  $graph
 * @param  array<string, true>  $seen
 */
function search(array $graph, string $node, string $goal, int $distance, array &$seen, int &$max): void
{
    if ($node === $goal) {
        $max = max($max, $distance);

        return;
    }

    $seen[$node] = true;

    foreach ($graph[$node] ?? [] as $next => $weight) {
        if (isset($seen[$next])) {
            continue;
        }

        search($graph, $next, $goal, $distance + $weight, $seen, $max);
    }

    unset($seen[$node]);
}

/**
 * @param  list<list<string>>  $grid
 * @return list<array{0: int, 1: int}>
 */
function legalMoves(array $grid, int $row, int $column, bool $respectSlopes): array
{
    if (! $respectSlopes) {
        return openNeighbors($grid, $row, $column);
    }

    $tile = $grid[$row][$column];
    $deltas = match ($tile) {
        '>' => [[0, 1]],
        '<' => [[0, -1]],
        'v' => [[1, 0]],
        '^' => [[-1, 0]],
        default => [[-1, 0], [1, 0], [0, -1], [0, 1]],
    };

    $moves = [];

    foreach ($deltas as [$deltaRow, $deltaColumn]) {
        $nextRow = $row + $deltaRow;
        $nextColumn = $column + $deltaColumn;

        if (! isset($grid[$nextRow][$nextColumn]) || $grid[$nextRow][$nextColumn] === '#') {
            continue;
        }

        $moves[] = [$nextRow, $nextColumn];
    }

    return $moves;
}

/**
 * Neighbors used only to find junctions, ignoring slope direction.
 *
 * @param  list<list<string>>  $grid
 * @return list<array{0: int, 1: int}>
 */
function openNeighbors(array $grid, int $row, int $column): array
{
    $neighbors = [];

    foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as [$deltaRow, $deltaColumn]) {
        $nextRow = $row + $deltaRow;
        $nextColumn = $column + $deltaColumn;

        if (! isset($grid[$nextRow][$nextColumn]) || $grid[$nextRow][$nextColumn] === '#') {
            continue;
        }

        $neighbors[] = [$nextRow, $nextColumn];
    }

    return $neighbors;
}

/**
 * @param  array{0: int, 1: int}  $point
 */
function keyOf(array $point): string
{
    return $point[0].','.$point[1];
}
