<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 18: Lavaduct Lagoon.
 *
 * Part 1 example answer is 62. Part 2 example answer is 952408144115.
 *
 * The lagoon is the trench plus its interior. Vertices come from walking the
 * dig plan. Shoelace gives twice the polygon area; Pick's theorem turns that
 * and the boundary length into the lava volume. Both answers fit in a signed
 * 64-bit integer, so the arithmetic stays in PHP ints.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$plans = loadPlans($path);

echo 'Advent of Code 2023 — Day 18: Lavaduct Lagoon', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($plans), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$part1 = lavaVolume($plans, false);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %15d cubic meters %8s\n", 'Part 1  dig plan', $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
$part2 = lavaVolume($plans, true);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %15d cubic meters %8s\n", 'Part 2  hex colors', $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Cubic meters of lava: interior tiles plus the trench.
 *
 * Walk the plan to the corner vertices. The shoelace sum is twice the
 * polygon area, so it stays an integer. Boundary length is the sum of the
 * step distances. Pick's theorem says
 *
 *   interior = abs(doubleArea) / 2 - boundary / 2 + 1
 *   lava     = interior + boundary
 *            = abs(doubleArea) / 2 + boundary / 2 + 1
 *
 * abs(doubleArea) and the boundary have the same parity, so the division
 * is exact.
 *
 * @param  list<array{direction: string, meters: int, color: string}>  $plans
 */
function lavaVolume(array $plans, bool $fromColor): int
{
    $x = 0;
    $y = 0;
    $doubleArea = 0;
    $boundary = 0;

    foreach ($plans as $plan) {
        [$stepX, $stepY, $meters] = $fromColor ? colorStep($plan['color']) : letterStep($plan['direction'], $plan['meters']);

        $nextX = $x + $stepX * $meters;
        $nextY = $y + $stepY * $meters;

        $doubleArea += $x * $nextY - $nextX * $y;
        $boundary += $meters;

        $x = $nextX;
        $y = $nextY;
    }

    return intdiv(abs($doubleArea) + $boundary, 2) + 1;
}

/**
 * Part 1: the direction letter and the decimal distance.
 *
 * @return array{0: int, 1: int, 2: int}
 */
function letterStep(string $direction, int $meters): array
{
    return [...unitStep($direction), $meters];
}

/**
 * Part 2: five hex digits of distance, then a direction digit.
 * 0 = R, 1 = D, 2 = L, 3 = U.
 *
 * @return array{0: int, 1: int, 2: int}
 */
function colorStep(string $color): array
{
    $meters = hexdec(substr($color, 0, 5));
    $direction = match ($color[5]) {
        '0' => 'R',
        '1' => 'D',
        '2' => 'L',
        '3' => 'U',
        default => throw new RuntimeException("Unknown color direction in #{$color}"),
    };

    if (!is_int($meters)) {
        throw new RuntimeException("Color distance #{$color} does not fit in a PHP int");
    }

    return [...unitStep($direction), $meters];
}

/**
 * Unit step. Y grows downward so D is positive; only the absolute shoelace
 * sum is used, so the axis orientation does not change the volume.
 *
 * @return array{0: int, 1: int}
 */
function unitStep(string $direction): array
{
    return match ($direction) {
        'R' => [1, 0],
        'D' => [0, 1],
        'L' => [-1, 0],
        'U' => [0, -1],
        default => throw new RuntimeException("Unknown direction {$direction}"),
    };
}

/**
 * @return list<array{direction: string, meters: int, color: string}>
 */
function loadPlans(string $path): array
{
    $raw = file_get_contents($path);

    if ($raw === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $raw = rtrim($raw, "\r\n");

    if ($raw === '') {
        return [];
    }

    $plans = [];

    foreach (explode("\n", $raw) as $line) {
        if (preg_match('/^([UDLR]) (\d+) \(#([0-9a-fA-F]{6})\)$/', $line, $match) !== 1) {
            throw new RuntimeException("Unrecognized dig plan: {$line}");
        }

        $plans[] = [
            'direction' => $match[1],
            'meters' => (int) $match[2],
            'color' => $match[3],
        ];
    }

    return $plans;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
