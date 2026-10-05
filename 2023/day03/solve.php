<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 3: Gear Ratios.
 *
 * Part 1 example answer is 4361. Part 2 example answer is 467835.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$grid = loadGrid($path);

echo 'Advent of Code 2023 — Day 3: Gear Ratios', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($grid), ' rows', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
[$partSum, $gearSum] = schematicSums($grid);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d part sum    %8s\n", 'Part 1  part numbers', $partSum, formatDuration($elapsed));
printf("  %-20s %6d gear ratio  %8s\n", 'Part 2  gears', $gearSum, formatDuration($elapsed));
echo PHP_EOL;

/**
 * A part number touches a symbol, including diagonally. A gear is a "*" that
 * touches exactly two part numbers; its ratio is their product.
 *
 * @param  list<string>  $grid
 * @return array{0: int, 1: int}
 */
function schematicSums(array $grid): array
{
    $height = count($grid);
    $width = strlen($grid[0]);
    $partSum = 0;
    $gears = [];

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            if (! ctype_digit($grid[$y][$x])) {
                continue;
            }

            $start = $x;
            $value = 0;

            while ($x < $width && ctype_digit($grid[$y][$x])) {
                $value = $value * 10 + (int) $grid[$y][$x];
                $x++;
            }

            $symbols = adjacentSymbols($grid, $y, $start, $x - 1);

            if ($symbols !== []) {
                $partSum += $value;
            }

            foreach ($symbols as $symbol) {
                if ($symbol['char'] !== '*') {
                    continue;
                }

                $gears[$symbol['y'].','.$symbol['x']][] = $value;
            }
        }
    }

    $gearSum = 0;

    foreach ($gears as $numbers) {
        if (count($numbers) === 2) {
            $gearSum += $numbers[0] * $numbers[1];
        }
    }

    return [$partSum, $gearSum];
}

/**
 * @param  list<string>  $grid
 * @return list<array{y: int, x: int, char: string}>
 */
function adjacentSymbols(array $grid, int $row, int $from, int $to): array
{
    $height = count($grid);
    $width = strlen($grid[0]);
    $symbols = [];

    for ($y = $row - 1; $y <= $row + 1; $y++) {
        for ($x = $from - 1; $x <= $to + 1; $x++) {
            if ($y < 0 || $x < 0 || $y >= $height || $x >= $width) {
                continue;
            }

            $char = $grid[$y][$x];

            if ($char === '.' || ctype_digit($char)) {
                continue;
            }

            $symbols[$y.','.$x] = ['y' => $y, 'x' => $x, 'char' => $char];
        }
    }

    return array_values($symbols);
}

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

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
