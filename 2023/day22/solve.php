<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 22: Sand Slabs.
 *
 * Paste the puzzle input into puzzle-input.txt.
 * Part 1 example answer is 5. Part 2 example answer is 7.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$bricks = loadBricks($path);
[$supports, $supportedBy] = settleAndSupport($bricks);

echo 'Advent of Code 2023 — Day 22: Sand Slabs', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($bricks), ' bricks', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$safe = countSafeToDisintegrate($supportedBy, count($bricks));
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %6d bricks    %8s\n", 'Part 1  safe to drop', $safe, formatDuration($elapsed));

$startedAt = hrtime(true);
$chain = totalChainReaction($supports, $supportedBy);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %6d falling   %8s\n", 'Part 2  chain reaction', $chain, formatDuration($elapsed));
echo PHP_EOL;

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}

/**
 * @return list<array{x1: int, y1: int, z1: int, x2: int, y2: int, z2: int}>
 */
function loadBricks(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $bricks = [];

    foreach ($lines as $line) {
        if (!preg_match('/^(\d+),(\d+),(\d+)~(\d+),(\d+),(\d+)$/', $line, $match)) {
            throw new RuntimeException("Bad brick: {$line}");
        }

        $x1 = (int) $match[1];
        $y1 = (int) $match[2];
        $z1 = (int) $match[3];
        $x2 = (int) $match[4];
        $y2 = (int) $match[5];
        $z2 = (int) $match[6];

        $bricks[] = [
            'x1' => min($x1, $x2),
            'y1' => min($y1, $y2),
            'z1' => min($z1, $z2),
            'x2' => max($x1, $x2),
            'y2' => max($y1, $y2),
            'z2' => max($z1, $z2),
        ];
    }

    return $bricks;
}

/**
 * Drop every brick once, lowest first, then record which bricks rest on which.
 *
 * A brick falls as a whole until its underside is one above the highest brick
 * that overlaps it in x and y, or onto the ground at z = 1.
 *
 * @param  list<array{x1: int, y1: int, z1: int, x2: int, y2: int, z2: int}>  $bricks
 * @return array{0: array<int, list<int>>, 1: array<int, list<int>>}
 */
function settleAndSupport(array $bricks): array
{
    usort($bricks, static fn (array $a, array $b): int => $a['z1'] <=> $b['z1']);

    $count = count($bricks);

    for ($i = 0; $i < $count; $i++) {
        $rest = 0;

        for ($j = 0; $j < $i; $j++) {
            if (!overlapsXy($bricks[$i], $bricks[$j])) {
                continue;
            }

            if ($bricks[$j]['z2'] > $rest) {
                $rest = $bricks[$j]['z2'];
            }
        }

        $height = $bricks[$i]['z2'] - $bricks[$i]['z1'];
        $bricks[$i]['z1'] = $rest + 1;
        $bricks[$i]['z2'] = $bricks[$i]['z1'] + $height;
    }

    /** @var array<int, list<int>> $supports */
    $supports = array_fill(0, $count, []);
    /** @var array<int, list<int>> $supportedBy */
    $supportedBy = array_fill(0, $count, []);

    for ($above = 0; $above < $count; $above++) {
        for ($below = 0; $below < $count; $below++) {
            if ($above === $below) {
                continue;
            }

            if ($bricks[$below]['z2'] + 1 !== $bricks[$above]['z1']) {
                continue;
            }

            if (!overlapsXy($bricks[$above], $bricks[$below])) {
                continue;
            }

            $supports[$below][] = $above;
            $supportedBy[$above][] = $below;
        }
    }

    return [$supports, $supportedBy];
}

/**
 * @param  array{x1: int, y1: int, z1: int, x2: int, y2: int, z2: int}  $a
 * @param  array{x1: int, y1: int, z1: int, x2: int, y2: int, z2: int}  $b
 */
function overlapsXy(array $a, array $b): bool
{
    return $a['x1'] <= $b['x2']
        && $b['x1'] <= $a['x2']
        && $a['y1'] <= $b['y2']
        && $b['y1'] <= $a['y2'];
}

/**
 * Bricks that are not the only support of any brick above them.
 *
 * @param  array<int, list<int>>  $supportedBy
 */
function countSafeToDisintegrate(array $supportedBy, int $count): int
{
    $unsafe = [];

    foreach ($supportedBy as $below) {
        if (count($below) === 1) {
            $unsafe[$below[0]] = true;
        }
    }

    return $count - count($unsafe);
}

/**
 * Sum, over every brick, of how many other bricks fall if that brick is removed.
 *
 * A brick falls when every brick supporting it has already fallen. Each brick
 * is enqueued at most once.
 *
 * @param  array<int, list<int>>  $supports
 * @param  array<int, list<int>>  $supportedBy
 */
function totalChainReaction(array $supports, array $supportedBy): int
{
    $total = 0;
    $count = count($supports);

    for ($start = 0; $start < $count; $start++) {
        $falling = [$start => true];
        $queue = [$start];
        $head = 0;

        while ($head < count($queue)) {
            $brick = $queue[$head];
            $head++;

            foreach ($supports[$brick] as $above) {
                if (isset($falling[$above])) {
                    continue;
                }

                $canStay = false;

                foreach ($supportedBy[$above] as $below) {
                    if (!isset($falling[$below])) {
                        $canStay = true;
                        break;
                    }
                }

                if ($canStay) {
                    continue;
                }

                $falling[$above] = true;
                $queue[] = $above;
                $total++;
            }
        }
    }

    return $total;
}
