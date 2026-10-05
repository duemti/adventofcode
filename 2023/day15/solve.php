<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 15: Lens Library.
 *
 * Part 1 example answer is 1320. Part 2 example answer is 145.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$steps = loadSteps($path);

echo 'Advent of Code 2023 — Day 15: Lens Library', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($steps), ' steps', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$hashSum = sumStepHashes($steps);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d hash sum %8s\n", 'Part 1  HASH', $hashSum, formatDuration($elapsed));

$startedAt = hrtime(true);
$focusingPower = focusingPower($steps);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d focusing %8s\n", 'Part 2  lenses', $focusingPower, formatDuration($elapsed));
echo PHP_EOL;

/**
 * HASH starts at 0. For each ASCII byte, add the byte, multiply by 17,
 * and keep the remainder modulo 256.
 */
function holidayHash(string $text): int
{
    $value = 0;
    $length = strlen($text);

    for ($index = 0; $index < $length; $index++) {
        $value += ord($text[$index]);
        $value *= 17;
        $value %= 256;
    }

    return $value;
}

/**
 * @param  list<string>  $steps
 */
function sumStepHashes(array $steps): int
{
    $sum = 0;

    foreach ($steps as $step) {
        $sum += holidayHash($step);
    }

    return $sum;
}

/**
 * Run the initialization sequence on 256 boxes and sum focusing power.
 *
 * Each box is an ordered list of lenses [label, focal]. `label-` removes
 * that lens from box HASH(label) when it is present. `label=N` replaces the
 * focal length in place when the label is already there, otherwise appends.
 * Focusing power of a lens is (boxIndex + 1) * (slotIndex + 1) * focal.
 *
 * @param  list<string>  $steps
 */
function focusingPower(array $steps): int
{
    /** @var array<int, list<array{0: string, 1: int}>> $boxes */
    $boxes = array_fill(0, 256, []);

    foreach ($steps as $step) {
        if (str_ends_with($step, '-')) {
            $label = substr($step, 0, -1);
            $box = holidayHash($label);
            $boxes[$box] = array_values(array_filter(
                $boxes[$box],
                static fn (array $lens): bool => $lens[0] !== $label,
            ));

            continue;
        }

        [$label, $focal] = explode('=', $step, 2);
        $focalLength = (int) $focal;
        $box = holidayHash($label);
        $replaced = false;

        foreach ($boxes[$box] as $slot => $lens) {
            if ($lens[0] === $label) {
                $boxes[$box][$slot][1] = $focalLength;
                $replaced = true;
                break;
            }
        }

        if (! $replaced) {
            $boxes[$box][] = [$label, $focalLength];
        }
    }

    $power = 0;

    foreach ($boxes as $boxIndex => $lenses) {
        foreach ($lenses as $slotIndex => $lens) {
            $power += ($boxIndex + 1) * ($slotIndex + 1) * $lens[1];
        }
    }

    return $power;
}

/**
 * One comma-separated line of initialization steps. Trailing newline is trimmed.
 *
 * @return list<string>
 */
function loadSteps(string $path): array
{
    $raw = file_get_contents($path);

    if ($raw === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $raw = rtrim($raw, "\r\n");

    if ($raw === '') {
        return [];
    }

    return explode(',', $raw);
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
