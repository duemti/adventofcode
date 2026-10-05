<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 17: Clumsy Crucible.
 *
 * Part 1 example answer is 102. Part 2 example answer is 94.
 *
 * Dijkstra over (row, column, incoming direction). From each state the next
 * move is a straight run of min..max blocks, costed as one edge. A state is
 * settled when it is taken from its heat-loss bucket.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);

echo 'Advent of Code 2023 — Day 17: Clumsy Crucible', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($grid), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$crucible = leastHeatLoss($grid, 1, 3);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d heat loss %8s\n", 'Part 1  crucible', $crucible, formatDuration($elapsed));

$startedAt = hrtime(true);
$ultra = leastHeatLoss($grid, 4, 10);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d heat loss %8s\n", 'Part 2  ultra', $ultra, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @return list<string>
 */
function loadGrid(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    return $lines;
}

/**
 * Minimum heat loss from the top-left to the bottom-right.
 *
 * Entering a block adds its digit. The crucible cannot reverse. Each move
 * is a straight run of $minRun to $maxRun blocks, so the factory is only
 * entered at the end of a legal run.
 *
 * @param  list<string>  $grid
 */
function leastHeatLoss(array $grid, int $minRun, int $maxRun): int
{
    $rows = count($grid);
    $cols = strlen($grid[0]);
    $goalRow = $rows - 1;
    $goalCol = $cols - 1;

    // East, south, west, north. Direction 4 is the starting state, which has no heading.
    $deltaRow = [0, 1, 0, -1];
    $deltaCol = [1, 0, -1, 0];

    // State bits: row, column, incoming direction.
    /** @var array<int, list<int>> $buckets */
    $buckets = [0 => [4]];

    /** @var array<int, true> $settled */
    $settled = [];
    $limit = ($rows + $cols) * 9;

    for ($cost = 0; $cost <= $limit; $cost++) {
        if (!isset($buckets[$cost])) {
            continue;
        }

        $layer = $buckets[$cost];
        unset($buckets[$cost]);

        foreach ($layer as $state) {
            if (isset($settled[$state])) {
                continue;
            }

            $settled[$state] = true;
            $direction = $state & 7;
            $col = ($state >> 3) & 65535;
            $row = $state >> 19;

            if ($row === $goalRow && $col === $goalCol) {
                return $cost;
            }

            for ($nextDirection = 0; $nextDirection < 4; $nextDirection++) {
                if ($direction < 4 && ($nextDirection === $direction || $nextDirection === ($direction ^ 2))) {
                    continue;
                }

                $nextCost = $cost;
                $nextRow = $row;
                $nextCol = $col;

                for ($distance = 1; $distance <= $maxRun; $distance++) {
                    $nextRow += $deltaRow[$nextDirection];
                    $nextCol += $deltaCol[$nextDirection];

                    if ($nextRow < 0 || $nextCol < 0 || $nextRow >= $rows || $nextCol >= $cols) {
                        break;
                    }

                    $nextCost += ord($grid[$nextRow][$nextCol]) - 48;

                    if ($distance < $minRun) {
                        continue;
                    }

                    $nextState = ($nextRow << 19) | ($nextCol << 3) | $nextDirection;

                    if (isset($settled[$nextState])) {
                        continue;
                    }

                    $buckets[$nextCost][] = $nextState;
                }
            }
        }
    }

    throw new RuntimeException('No route reaches the factory');
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
