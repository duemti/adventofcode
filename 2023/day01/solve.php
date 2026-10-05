<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 1: Trebuchet?!
 *
 * Part 1 example answer is 142. Part 2 example answer is 281.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 1: Trebuchet?!', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$digitsOnly = sumCalibrations($lines, false);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d calibration %8s\n", 'Part 1  digits', $digitsOnly, formatDuration($elapsed));

$startedAt = hrtime(true);
$withWords = sumCalibrations($lines, true);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d calibration %8s\n", 'Part 2  spelled', $withWords, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $lines
 */
function sumCalibrations(array $lines, bool $spelled): int
{
    $sum = 0;

    foreach ($lines as $line) {
        $sum += calibrationValue($line, $spelled);
    }

    return $sum;
}

function calibrationValue(string $line, bool $spelled): int
{
    $words = [
        'one' => '1',
        'two' => '2',
        'three' => '3',
        'four' => '4',
        'five' => '5',
        'six' => '6',
        'seven' => '7',
        'eight' => '8',
        'nine' => '9',
    ];

    $found = [];
    $length = strlen($line);

    for ($index = 0; $index < $length; $index++) {
        if (ctype_digit($line[$index])) {
            $found[] = $line[$index];

            continue;
        }

        if (! $spelled) {
            continue;
        }

        foreach ($words as $word => $digit) {
            if (substr($line, $index, strlen($word)) === $word) {
                $found[] = $digit;
                break;
            }
        }
    }

    if ($found === []) {
        return 0;
    }

    return (int) ($found[0].$found[array_key_last($found)]);
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
