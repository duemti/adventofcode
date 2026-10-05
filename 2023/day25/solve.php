<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 25: Snowverload.
 *
 * Paste the puzzle input into puzzle-input.txt.
 * Part 1 example answer is 54. Part 2 is not scored.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
[$edges, $componentCount] = loadGraph($path);

echo 'Advent of Code 2023 — Day 25: Snowverload', PHP_EOL;
echo 'Input: ', basename($path), '   ', $componentCount, ' components', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$product = snowverload($edges, $componentCount);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d product    %8s\n", 'Part 1  snowverload', $product, formatDuration($elapsed));

$startedAt = hrtime(true);
$partTwo = 0;
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d            %8s\n", 'Part 2  none', $partTwo, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Product of the two group sizes after cutting exactly three wires.
 *
 * Grow one group by always pulling in the outside component with the most
 * wires already into the group. The loop adds each component at most once.
 * The first time exactly three wires leave the group, that split is the cut.
 *
 * @param  list<array{0: int, 1: int}>  $edges
 */
function snowverload(array $edges, int $componentCount): int
{
    $neighbors = array_fill(0, $componentCount, []);

    foreach ($edges as [$left, $right]) {
        $neighbors[$left][] = $right;
        $neighbors[$right][] = $left;
    }

    $inside = array_fill(0, $componentCount, false);
    $inside[0] = true;
    $links = array_fill(0, $componentCount, 0);

    foreach ($neighbors[0] as $neighbor) {
        $links[$neighbor] = 1;
    }

    $cut = count($neighbors[0]);
    $insideCount = 1;

    while ($insideCount < $componentCount) {
        if ($cut === 3) {
            return $insideCount * ($componentCount - $insideCount);
        }

        $next = -1;
        $nextLinks = -1;

        for ($component = 1; $component < $componentCount; $component++) {
            if ($inside[$component] || $links[$component] <= $nextLinks) {
                continue;
            }

            $next = $component;
            $nextLinks = $links[$component];
        }

        if ($next < 0) {
            break;
        }

        $inside[$next] = true;
        $insideCount++;
        $cut += count($neighbors[$next]) - (2 * $nextLinks);

        foreach ($neighbors[$next] as $neighbor) {
            if (! $inside[$neighbor]) {
                $links[$neighbor]++;
            }
        }
    }

    throw new RuntimeException('No 3-wire cut found while growing the group.');
}

/**
 * Undirected wiring diagram. Each line is `name: other other other`.
 * Duplicate edges are dropped.
 *
 * @return array{0: list<array{0: int, 1: int}>, 1: int}
 */
function loadGraph(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $ids = [];
    $seen = [];
    $edges = [];

    foreach ($lines as $line) {
        [$name, $rest] = explode(': ', $line, 2);

        foreach (explode(' ', $rest) as $other) {
            if ($other === '' || $other === $name) {
                continue;
            }

            $left = $name;
            $right = $other;

            if ($left > $right) {
                [$left, $right] = [$right, $left];
            }

            $key = $left.' '.$right;

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            if (! isset($ids[$left])) {
                $ids[$left] = count($ids);
            }

            if (! isset($ids[$right])) {
                $ids[$right] = count($ids);
            }

            $edges[] = [$ids[$left], $ids[$right]];
        }
    }

    return [$edges, count($ids)];
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
