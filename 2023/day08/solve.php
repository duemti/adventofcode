<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 8: Haunted Wasteland
 *
 * Part 1 example answer is 2. Part 2 example answer is 6.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);
[$instructions, $network] = parseMap($lines);

echo 'Advent of Code 2023 — Day 8: Haunted Wasteland', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$part1 = stepsUntil($network, $instructions, 'AAA', static fn (string $node): bool => $node === 'ZZZ');
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d steps %8s\n", 'Part 1  AAA', $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
$part2 = ghostSteps($network, $instructions);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d steps %8s\n", 'Part 2  ghosts', $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $lines
 * @return array{0: string, 1: array<string, array{0: string, 1: string}>}
 */
function parseMap(array $lines): array
{
    $instructions = array_shift($lines);

    if (! is_string($instructions) || $instructions === '' || preg_match('/^[LR]+$/', $instructions) !== 1) {
        throw new RuntimeException('Missing L/R instructions');
    }

    $network = [];

    foreach ($lines as $line) {
        if (preg_match('/^([A-Z0-9]+)\s*=\s*\(([A-Z0-9]+),\s*([A-Z0-9]+)\)$/', $line, $match) !== 1) {
            throw new RuntimeException("Bad node line: {$line}");
        }

        $network[$match[1]] = [$match[2], $match[3]];
    }

    return [$instructions, $network];
}

/**
 * @param  array<string, array{0: string, 1: string}>  $network
 * @param  callable(string): bool  $done
 */
function stepsUntil(array $network, string $instructions, string $node, callable $done): int
{
    $length = strlen($instructions);
    $steps = 0;
    $seen = [];

    while (! $done($node)) {
        $mark = $node."\0".($steps % $length);

        if (isset($seen[$mark])) {
            throw new RuntimeException("Cycle at {$node} before the destination");
        }

        $seen[$mark] = true;
        $turn = $instructions[$steps % $length];
        $next = $network[$node][$turn === 'L' ? 0 : 1] ?? null;

        if ($next === null) {
            throw new RuntimeException("No {$turn} exit from {$node}");
        }

        $node = $next;
        $steps++;
    }

    return $steps;
}

/**
 * Each ghost starts on a node ending in A. On these maps the first time a ghost
 * reaches a node ending in Z is also the length of its cycle, so the step count
 * when every ghost is on a Z node is the LCM of those arrival times.
 *
 * @param  array<string, array{0: string, 1: string}>  $network
 */
function ghostSteps(array $network, string $instructions): int
{
    $steps = 1;

    foreach (array_keys($network) as $node) {
        if (! str_ends_with($node, 'A')) {
            continue;
        }

        $arrival = stepsUntil(
            $network,
            $instructions,
            $node,
            static fn (string $current): bool => str_ends_with($current, 'Z'),
        );
        $steps = leastCommonMultiple($steps, $arrival);
    }

    return $steps;
}

function leastCommonMultiple(int $left, int $right): int
{
    if ($left === 0 || $right === 0) {
        return 0;
    }

    return intdiv($left, greatestCommonDivisor($left, $right)) * $right;
}

function greatestCommonDivisor(int $left, int $right): int
{
    while ($right !== 0) {
        [$left, $right] = [$right, $left % $right];
    }

    return $left;
}

/**
 * @return list<string>
 */
function loadLines(string $path): array
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
