<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 12: Hot Springs.
 *
 * Part 1 example answer is 21. Part 2 example answer is 525152.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 12: Hot Springs', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$part1 = sumArrangements($lines, 1);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %16d arrangements %8s\n", 'Part 1  records', $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
$part2 = sumArrangements($lines, 5);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %16d arrangements %8s\n", 'Part 2  unfolded', $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Sum valid replacements of ? on each row.
 *
 * $folds is 1 for the record as written. For part 2 it is 5: the springs
 * string is repeated, joined by ?, and the damaged-group list is repeated.
 *
 * @param  list<string>  $lines
 */
function sumArrangements(array $lines, int $folds): int
{
    $total = 0;

    foreach ($lines as $line) {
        [$springs, $groups] = parseRecord($line);
        [$springs, $groups] = unfold($springs, $groups, $folds);
        $total += countArrangements($springs, $groups);
    }

    return $total;
}

/**
 * @return array{0: string, 1: list<int>}
 */
function parseRecord(string $line): array
{
    [$springs, $groupText] = explode(' ', trim($line), 2);
    $groups = array_map(intval(...), explode(',', $groupText));

    return [$springs, $groups];
}

/**
 * Repeat a row $folds times. Springs copies are joined by ?.
 *
 * @param  list<int>  $groups
 * @return array{0: string, 1: list<int>}
 */
function unfold(string $springs, array $groups, int $folds): array
{
    if ($folds === 1) {
        return [$springs, $groups];
    }

    $springCopies = [];
    $groupCopies = [];

    for ($fold = 0; $fold < $folds; $fold++) {
        $springCopies[] = $springs;

        foreach ($groups as $group) {
            $groupCopies[] = $group;
        }
    }

    return [implode('?', $springCopies), $groupCopies];
}

/**
 * Ways to replace each ? with . or # so damaged runs match $groups in order.
 *
 * Recursive DP over (index in the springs, index of the next group). Each
 * state is stored once under a packed integer key. Leading and trailing
 * operational springs are allowed; groups must be separated by at least one.
 *
 * @param  list<int>  $groups
 */
function countArrangements(string $springs, array $groups): int
{
    $groupCount = count($groups);
    $minSpan = [];
    $span = 0;

    for ($group = $groupCount - 1; $group >= 0; $group--) {
        $span += $groups[$group];

        if ($group < $groupCount - 1) {
            $span++;
        }

        $minSpan[$group] = $span;
    }

    $memo = [];

    return arrangements($springs, $groups, strlen($springs), $groupCount, $minSpan, 0, 0, $memo);
}

/**
 * @param  list<int>  $groups
 * @param  array<int, int>  $minSpan  Minimum springs still required, including gaps.
 * @param  array<int, int>  $memo
 */
function arrangements(
    string $springs,
    array $groups,
    int $length,
    int $groupCount,
    array $minSpan,
    int $index,
    int $groupIndex,
    array &$memo,
): int {
    $key = ($index << 12) | $groupIndex;

    if (isset($memo[$key])) {
        return $memo[$key];
    }

    if ($groupIndex === $groupCount) {
        $ways = 1;

        for ($cursor = $index; $cursor < $length; $cursor++) {
            if ($springs[$cursor] === '#') {
                $ways = 0;
                break;
            }
        }

        return $memo[$key] = $ways;
    }

    if ($index >= $length || $length - $index < $minSpan[$groupIndex]) {
        return $memo[$key] = 0;
    }

    $ways = 0;
    $cell = $springs[$index];

    // An operational spring (or an unknown used as one) cannot start a group.
    if ($cell !== '#') {
        $ways += arrangements(
            $springs,
            $groups,
            $length,
            $groupCount,
            $minSpan,
            $index + 1,
            $groupIndex,
            $memo,
        );
    }

    // Place the next damaged group on this cell when every spring in the run can be damaged.
    if ($cell !== '.') {
        $size = $groups[$groupIndex];
        $end = $index + $size;

        if ($end <= $length) {
            $dot = strpos($springs, '.', $index);
            $fits = $dot === false || $dot >= $end;
            $separated = $end === $length || $springs[$end] !== '#';

            if ($fits && $separated) {
                $ways += arrangements(
                    $springs,
                    $groups,
                    $length,
                    $groupCount,
                    $minSpan,
                    $end + 1,
                    $groupIndex + 1,
                    $memo,
                );
            }
        }
    }

    return $memo[$key] = $ways;
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
