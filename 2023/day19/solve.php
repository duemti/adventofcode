<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 19: Aplenty.
 *
 * Part 1 example answer is 19114. Part 2 example answer is 167409079868000.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
[$workflows, $parts] = loadSystem($path);

echo 'Advent of Code 2023 — Day 19: Aplenty', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($workflows), ' workflows, ', count($parts), ' parts', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$ratingSum = sumAcceptedRatings($workflows, $parts);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d rating sum %8s\n", 'Part 1  parts', $ratingSum, formatDuration($elapsed));

$startedAt = hrtime(true);
$combinations = countAcceptedCombinations($workflows);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d combinations %8s\n", 'Part 2  ranges', $combinations, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Sum x+m+a+s for every part that workflow "in" accepts.
 *
 * @param  array<string, list<array{cat?: string, op?: string, n?: int, dest: string}>>  $workflows
 * @param  list<array{x: int, m: int, a: int, s: int}>  $parts
 */
function sumAcceptedRatings(array $workflows, array $parts): int
{
    $sum = 0;

    foreach ($parts as $part) {
        if (isAccepted($workflows, $part)) {
            $sum += $part['x'] + $part['m'] + $part['a'] + $part['s'];
        }
    }

    return $sum;
}

/**
 * Follow rules from workflow "in" until the part is accepted or rejected.
 *
 * @param  array<string, list<array{cat?: string, op?: string, n?: int, dest: string}>>  $workflows
 * @param  array{x: int, m: int, a: int, s: int}  $part
 */
function isAccepted(array $workflows, array $part): bool
{
    $name = 'in';

    while ($name !== 'A' && $name !== 'R') {
        foreach ($workflows[$name] as $rule) {
            if (!isset($rule['cat'])) {
                $name = $rule['dest'];
                continue 2;
            }

            $value = $part[$rule['cat']];
            $matches = $rule['op'] === '<'
                ? $value < $rule['n']
                : $value > $rule['n'];

            if ($matches) {
                $name = $rule['dest'];
                continue 2;
            }
        }
    }

    return $name === 'A';
}

/**
 * Count rating combinations in 1..4000 that workflow "in" accepts.
 *
 * Each category keeps an inclusive range. A comparison splits that range into
 * the slice that matches and the slice that falls through. Accepted volume is
 * the product of the four range lengths. The full space is 4000^4, which fits
 * in a signed 64-bit integer.
 *
 * @param  array<string, list<array{cat?: string, op?: string, n?: int, dest: string}>>  $workflows
 */
function countAcceptedCombinations(array $workflows): int
{
    $ranges = [
        'x' => [1, 4000],
        'm' => [1, 4000],
        'a' => [1, 4000],
        's' => [1, 4000],
    ];

    return acceptedVolume($workflows, 'in', $ranges);
}

/**
 * @param  array<string, list<array{cat?: string, op?: string, n?: int, dest: string}>>  $workflows
 * @param  array<string, array{0: int, 1: int}>  $ranges
 */
function acceptedVolume(array $workflows, string $name, array $ranges): int
{
    if ($name === 'R') {
        return 0;
    }

    if ($name === 'A') {
        return rangeVolume($ranges);
    }

    $total = 0;

    foreach ($workflows[$name] as $rule) {
        if (!isset($rule['cat'])) {
            $total += acceptedVolume($workflows, $rule['dest'], $ranges);
            break;
        }

        [$match, $rest] = splitRange($ranges[$rule['cat']], $rule['op'], $rule['n']);

        if ($match[0] <= $match[1]) {
            $next = $ranges;
            $next[$rule['cat']] = $match;
            $total += acceptedVolume($workflows, $rule['dest'], $next);
        }

        if ($rest[0] > $rest[1]) {
            break;
        }

        $ranges[$rule['cat']] = $rest;
    }

    return $total;
}

/**
 * Split an inclusive range on a comparison.
 *
 * "< n" keeps values below n. "> n" keeps values above n. The other slice
 * is what later rules in the same workflow still see.
 *
 * @param  array{0: int, 1: int}  $range
 * @return array{0: array{0: int, 1: int}, 1: array{0: int, 1: int}}
 */
function splitRange(array $range, string $operator, int $threshold): array
{
    [$lo, $hi] = $range;

    if ($operator === '<') {
        return [
            [$lo, min($hi, $threshold - 1)],
            [max($lo, $threshold), $hi],
        ];
    }

    return [
        [max($lo, $threshold + 1), $hi],
        [$lo, min($hi, $threshold)],
    ];
}

/**
 * @param  array<string, array{0: int, 1: int}>  $ranges
 */
function rangeVolume(array $ranges): int
{
    $volume = 1;

    foreach ($ranges as [$lo, $hi]) {
        $volume *= $hi - $lo + 1;
    }

    return $volume;
}

/**
 * @return array{
 *     0: array<string, list<array{cat?: string, op?: string, n?: int, dest: string}>>,
 *     1: list<array{x: int, m: int, a: int, s: int}>
 * }
 */
function loadSystem(string $path): array
{
    $text = file_get_contents($path);
    if ($text === false) {
        fwrite(STDERR, "Cannot read {$path}\n");
        exit(1);
    }

    [$workflowText, $partText] = explode("\n\n", trim($text), 2);

    $workflows = [];
    foreach (explode("\n", $workflowText) as $line) {
        if (!preg_match('/^([a-z]+)\{([^}]+)\}$/', $line, $match)) {
            fwrite(STDERR, "Bad workflow: {$line}\n");
            exit(1);
        }

        $rules = [];
        foreach (explode(',', $match[2]) as $ruleText) {
            if (preg_match('/^([xmas])([<>])(\d+):([a-zAR]+)$/', $ruleText, $rule)) {
                $rules[] = [
                    'cat' => $rule[1],
                    'op' => $rule[2],
                    'n' => (int) $rule[3],
                    'dest' => $rule[4],
                ];
                continue;
            }

            $rules[] = ['dest' => $ruleText];
        }

        $workflows[$match[1]] = $rules;
    }

    $parts = [];
    foreach (explode("\n", $partText) as $line) {
        if ($line === '') {
            continue;
        }

        if (!preg_match('/^\{x=(\d+),m=(\d+),a=(\d+),s=(\d+)\}$/', $line, $part)) {
            fwrite(STDERR, "Bad part: {$line}\n");
            exit(1);
        }

        $parts[] = [
            'x' => (int) $part[1],
            'm' => (int) $part[2],
            'a' => (int) $part[3],
            's' => (int) $part[4],
        ];
    }

    return [$workflows, $parts];
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
