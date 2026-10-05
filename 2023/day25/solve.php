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
 * Each attempt is one run of Karger's algorithm: contract a random edge
 * until two supernodes remain. Stop at the first cut of size 3. 200 attempts
 * is the hard cap.
 *
 * @param  list<array{0: int, 1: int}>  $edges
 */
function snowverload(array $edges, int $componentCount): int
{
    for ($attempt = 0; $attempt < 200; $attempt++) {
        [$cut, $product] = contractOnce($edges, $componentCount);

        if ($cut === 3) {
            return $product;
        }
    }

    throw new RuntimeException('No 3-wire cut found in 200 Karger attempts.');
}

/**
 * Contract random edges until two supernodes remain.
 *
 * Edges are sampled uniformly. A sampled edge whose ends already lie in the
 * same supernode is a self-loop and is discarded. Surviving edges are merged
 * with union-find. The cut size is the number of original edges that still
 * cross the two supernodes.
 *
 * @param  list<array{0: int, 1: int}>  $edges
 * @return array{0: int, 1: int}
 */
function contractOnce(array $edges, int $componentCount): array
{
    $parent = range(0, $componentCount - 1);
    $size = array_fill(0, $componentCount, 1);
    $pool = $edges;
    $poolCount = count($pool);
    $components = $componentCount;

    while ($components > 2 && $poolCount > 0) {
        $index = mt_rand(0, $poolCount - 1);
        $left = find($parent, $pool[$index][0]);
        $right = find($parent, $pool[$index][1]);

        if ($left === $right) {
            $poolCount--;
            $pool[$index] = $pool[$poolCount];

            continue;
        }

        if ($size[$left] < $size[$right]) {
            [$left, $right] = [$right, $left];
        }

        $parent[$right] = $left;
        $size[$left] += $size[$right];
        $components--;
    }

    if ($components !== 2) {
        return [0, 0];
    }

    $cut = 0;

    foreach ($edges as $edge) {
        if (find($parent, $edge[0]) !== find($parent, $edge[1])) {
            $cut++;
        }
    }

    $roots = [];

    for ($node = 0; $node < $componentCount; $node++) {
        $root = find($parent, $node);
        $roots[$root] = $size[$root];
    }

    $product = 1;

    foreach ($roots as $rootSize) {
        $product *= $rootSize;
    }

    return [$cut, $product];
}

/**
 * @param  array<int, int>  $parent
 */
function find(array &$parent, int $node): int
{
    while ($parent[$node] !== $node) {
        $parent[$node] = $parent[$parent[$node]];
        $node = $parent[$node];
    }

    return $node;
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
