<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 5: If You Give A Seed A Fertilizer
 *
 * Part 1 example answer is 35. Part 2 example answer is 46.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$almanac = loadAlmanac($path);

echo 'Advent of Code 2023 — Day 5: If You Give A Seed A Fertilizer', PHP_EOL;
echo 'Input: ', basename($path), '   ', $almanac['lineCount'], ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$lowestSeed = lowestLocation($almanac['seeds'], $almanac['maps']);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d location %8s\n", 'Part 1  seeds', $lowestSeed, formatDuration($elapsed));

$startedAt = hrtime(true);
$lowestRange = lowestLocationRanges($almanac['seeds'], $almanac['maps']);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d location %8s\n", 'Part 2  ranges', $lowestRange, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Each seed is one number. Maps are applied in almanac order.
 *
 * @param  list<int>  $seeds
 * @param  list<list<array{0: int, 1: int, 2: int}>>  $maps
 */
function lowestLocation(array $seeds, array $maps): int
{
    $lowest = PHP_INT_MAX;

    foreach ($seeds as $seed) {
        $value = $seed;

        foreach ($maps as $rules) {
            $value = mapValue($value, $rules);
        }

        if ($value < $lowest) {
            $lowest = $value;
        }
    }

    return $lowest;
}

/**
 * Seeds are start/length pairs. Intervals are split against each map.
 *
 * @param  list<int>  $seeds
 * @param  list<list<array{0: int, 1: int, 2: int}>>  $maps
 */
function lowestLocationRanges(array $seeds, array $maps): int
{
    $intervals = [];
    $count = count($seeds);

    for ($index = 0; $index < $count; $index += 2) {
        $start = $seeds[$index];
        $length = $seeds[$index + 1];
        $intervals[] = [$start, $start + $length];
    }

    foreach ($maps as $rules) {
        $intervals = applyMap($intervals, $rules);
    }

    $lowest = PHP_INT_MAX;

    foreach ($intervals as [$start]) {
        if ($start < $lowest) {
            $lowest = $start;
        }
    }

    return $lowest;
}

/**
 * @param  list<array{0: int, 1: int, 2: int}>  $rules  source start, exclusive source end, destination start
 */
function mapValue(int $value, array $rules): int
{
    foreach ($rules as [$sourceStart, $sourceEnd, $destinationStart]) {
        if ($value >= $sourceStart && $value < $sourceEnd) {
            return $destinationStart + ($value - $sourceStart);
        }
    }

    return $value;
}

/**
 * Map half-open intervals [start, end) through one almanac map.
 * Numbers outside every source range stay unchanged.
 *
 * @param  list<array{0: int, 1: int}>  $intervals
 * @param  list<array{0: int, 1: int, 2: int}>  $rules
 * @return list<array{0: int, 1: int}>
 */
function applyMap(array $intervals, array $rules): array
{
    $mapped = [];

    foreach ($intervals as [$start, $end]) {
        $cursor = $start;

        foreach ($rules as [$sourceStart, $sourceEnd, $destinationStart]) {
            if ($sourceEnd <= $cursor) {
                continue;
            }

            if ($sourceStart >= $end) {
                break;
            }

            if ($sourceStart > $cursor) {
                $gapEnd = min($sourceStart, $end);
                $mapped[] = [$cursor, $gapEnd];
                $cursor = $gapEnd;
            }

            $overlapStart = max($cursor, $sourceStart);
            $overlapEnd = min($end, $sourceEnd);

            if ($overlapStart < $overlapEnd) {
                $offset = $destinationStart - $sourceStart;
                $mapped[] = [$overlapStart + $offset, $overlapEnd + $offset];
                $cursor = $overlapEnd;
            }

            if ($cursor >= $end) {
                break;
            }
        }

        if ($cursor < $end) {
            $mapped[] = [$cursor, $end];
        }
    }

    return mergeIntervals($mapped);
}

/**
 * @param  list<array{0: int, 1: int}>  $intervals
 * @return list<array{0: int, 1: int}>
 */
function mergeIntervals(array $intervals): array
{
    if ($intervals === []) {
        return [];
    }

    usort($intervals, static fn (array $left, array $right): int => $left[0] <=> $right[0]);

    $merged = [];

    foreach ($intervals as [$start, $end]) {
        if ($merged === []) {
            $merged[] = [$start, $end];

            continue;
        }

        $last = array_key_last($merged);

        if ($start <= $merged[$last][1]) {
            $merged[$last][1] = max($merged[$last][1], $end);

            continue;
        }

        $merged[] = [$start, $end];
    }

    return $merged;
}

/**
 * @return array{seeds: list<int>, maps: list<list<array{0: int, 1: int, 2: int}>>, lineCount: int}
 */
function loadAlmanac(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    if ($lines !== [] && end($lines) === '') {
        array_pop($lines);
    }

    $seeds = [];
    $maps = [];
    $rules = null;

    foreach ($lines as $line) {
        if ($line === '') {
            if ($rules !== null) {
                $maps[] = sortRules($rules);
                $rules = null;
            }

            continue;
        }

        if (str_starts_with($line, 'seeds:')) {
            $seeds = array_map(intval(...), preg_split('/\s+/', trim(substr($line, 6))) ?: []);

            continue;
        }

        if (str_ends_with($line, 'map:')) {
            if ($rules !== null) {
                $maps[] = sortRules($rules);
            }

            $rules = [];

            continue;
        }

        if ($rules === null) {
            throw new RuntimeException("Unexpected line: {$line}");
        }

        $parts = preg_split('/\s+/', trim($line));

        if ($parts === false || count($parts) !== 3) {
            throw new RuntimeException("Unexpected map line: {$line}");
        }

        $destinationStart = (int) $parts[0];
        $sourceStart = (int) $parts[1];
        $length = (int) $parts[2];
        $rules[] = [$sourceStart, $sourceStart + $length, $destinationStart];
    }

    if ($rules !== null) {
        $maps[] = sortRules($rules);
    }

    return [
        'seeds' => $seeds,
        'maps' => $maps,
        'lineCount' => count($lines),
    ];
}

/**
 * @param  list<array{0: int, 1: int, 2: int}>  $rules
 * @return list<array{0: int, 1: int, 2: int}>
 */
function sortRules(array $rules): array
{
    usort($rules, static fn (array $left, array $right): int => $left[0] <=> $right[0]);

    return $rules;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
