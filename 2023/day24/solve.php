<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 24: Never Tell Me The Odds.
 *
 * Paste the puzzle input into puzzle-input.txt.
 * Part 1 example answer is 2. Part 2 example answer is 47.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$stones = loadHailstones($path);
[$min, $max] = testArea($path);

echo 'Advent of Code 2023 — Day 24: Never Tell Me The Odds', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($stones), ' hailstones', PHP_EOL;
echo 'Test area: ', number_format($min), ' .. ', number_format($max), PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$intersections = countFutureIntersections($stones, $min, $max);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d crossings   %8s\n", 'Part 1  future paths', $intersections, formatDuration($elapsed));

$startedAt = hrtime(true);
$rockSum = rockPositionSum($stones);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d position sum %8s\n", 'Part 2  thrown rock', $rockSum, formatDuration($elapsed));
echo PHP_EOL;

/**
 * How many pairs of hailstones have future XY paths that cross inside the test area.
 *
 * Ignore Z. Both stones must reach the crossing at time >= 0, and both X and Y
 * must lie in [$min, $max]. Parallel paths do not count.
 *
 * @param  list<array{px: int, py: int, pz: int, vx: int, vy: int, vz: int}>  $stones
 */
function countFutureIntersections(array $stones, int $min, int $max): int
{
    $count = 0;
    $total = count($stones);

    for ($i = 0; $i < $total; $i++) {
        for ($j = $i + 1; $j < $total; $j++) {
            $a = $stones[$i];
            $b = $stones[$j];

            // Paths in the XY plane:
            //   x = px + vx * t
            //   y = py + vy * t
            //
            // Set A's position at tA equal to B's position at tB and solve
            // for both times. The velocity cross product is the denominator;
            // when it is 0 the paths are parallel and this pair does not count.
            //
            // Accept the pair only when tA >= 0, tB >= 0, and the crossing
            // (px + vx * t) is inside [$min, $max] on both axes.

            $dx = $b['px'] - $a['px'];
            $dy = $b['py'] - $a['py'];

            $denominator = $a['vx'] * $b['vy'] - $a['vy'] * $b['vx'];
            if ($denominator === 0) {
                continue;
            }

            $tA = ($dx * $b['vy'] - $dy * $b['vx']) / $denominator;
            $tB = ($dx * $a['vy'] - $dy * $a['vx']) / $denominator;

            if ($tA < 0 || $tB < 0) {
                continue;
            }

            $x = $a['px'] + $a['vx'] * $tA;
            $y = $a['py'] + $a['vy'] * $tA;

            if ($x < $min || $x > $max || $y < $min || $y > $max) {
                continue;
            }

            $count++;
        }
    }

    return $count;
}

/**
 * Sum of the thrown rock's starting X, Y, and Z.
 *
 * The rock has an unknown position and velocity. For every hailstone there is
 * a time t (each stone can have its own t, and t >= 0) where the rock and that
 * stone occupy the same point:
 *
 *   rock + rockVelocity * t = hail + hailVelocity * t
 *
 * Z counts this time. There is no test area. Three hailstones are enough to
 * determine the six unknowns; every other stone must agree.
 *
 * @param  list<array{px: int, py: int, pz: int, vx: int, vy: int, vz: int}>  $stones
 */
function rockPositionSum(array $stones): int
{
    $rows = [];

    for ($index = 1; $index <= 4; $index++) {
        foreach (collisionRows($stones[0], $stones[$index]) as $row) {
            $rows[] = $row;
        }
    }

    [$px, $py, $pz] = solveRockSystem($rows);

    return (int) bcadd(bcadd($px, $py, 0), $pz, 0);
}

/**
 * At a shared collision, (rock - hail) is parallel to (hailVelocity - rockVelocity),
 * so their cross product is 0. Subtracting that identity for two hailstones
 * cancels the rock-only term and leaves three linear equations.
 *
 * @param  array{px: int, py: int, pz: int, vx: int, vy: int, vz: int}  $a
 * @param  array{px: int, py: int, pz: int, vx: int, vy: int, vz: int}  $b
 * @return list<array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int}>
 */
function collisionRows(array $a, array $b): array
{
    $dix = $a['vx'] - $b['vx'];
    $diy = $a['vy'] - $b['vy'];
    $diz = $a['vz'] - $b['vz'];
    $dpx = $a['px'] - $b['px'];
    $dpy = $a['py'] - $b['py'];
    $dpz = $a['pz'] - $b['pz'];

    $ax = $a['py'] * $a['vz'] - $a['pz'] * $a['vy'];
    $ay = $a['pz'] * $a['vx'] - $a['px'] * $a['vz'];
    $az = $a['px'] * $a['vy'] - $a['py'] * $a['vx'];
    $bx = $b['py'] * $b['vz'] - $b['pz'] * $b['vy'];
    $by = $b['pz'] * $b['vx'] - $b['px'] * $b['vz'];
    $bz = $b['px'] * $b['vy'] - $b['py'] * $b['vx'];

    return [
        [0, $diz, -$diy, 0, -$dpz, $dpy, $ax - $bx],
        [-$diz, 0, $dix, $dpz, 0, -$dpx, $ay - $by],
        [$diy, -$dix, 0, -$dpy, $dpx, 0, $az - $bz],
    ];
}

/**
 * Solve the six rock unknowns. Extra rows must agree with the same rock.
 *
 * @param  list<array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int}>  $rows
 * @return array{0: numeric-string, 1: numeric-string, 2: numeric-string}
 */
function solveRockSystem(array $rows): array
{
    bcscale(40);

    $height = count($rows);
    $matrix = [];

    foreach ($rows as $row) {
        $matrix[] = array_map(static fn (int $value): string => (string) $value, $row);
    }

    $pivotRow = array_fill(0, 6, -1);
    $row = 0;

    for ($column = 0; $column < 6 && $row < $height; $column++) {
        $selected = $row;

        for ($candidate = $row + 1; $candidate < $height; $candidate++) {
            if (bccomp(ltrim($matrix[$candidate][$column], '-'), ltrim($matrix[$selected][$column], '-')) > 0) {
                $selected = $candidate;
            }
        }

        if (bccomp($matrix[$selected][$column], '0') === 0) {
            continue;
        }

        [$matrix[$row], $matrix[$selected]] = [$matrix[$selected], $matrix[$row]];
        $pivotRow[$column] = $row;
        $pivot = $matrix[$row][$column];

        for ($other = 0; $other < $height; $other++) {
            if ($other === $row || bccomp($matrix[$other][$column], '0') === 0) {
                continue;
            }

            $factor = bcdiv($matrix[$other][$column], $pivot);

            for ($entry = $column; $entry <= 6; $entry++) {
                $matrix[$other][$entry] = bcsub($matrix[$other][$entry], bcmul($factor, $matrix[$row][$entry]));
            }
        }

        $row++;
    }

    $position = [];

    for ($column = 0; $column < 3; $column++) {
        if ($pivotRow[$column] === -1) {
            throw new RuntimeException('Rock system is singular; try different hailstones.');
        }

        $value = bcdiv($matrix[$pivotRow[$column]][6], $matrix[$pivotRow[$column]][$column]);
        $position[] = bccomp($value, '0') >= 0
            ? bcadd($value, '0.5', 0)
            : bcsub($value, '0.5', 0);
    }

    return [$position[0], $position[1], $position[2]];
}

/**
 * Example input uses the small window from the statement. The real input uses the large one.
 *
 * @return array{0: int, 1: int}
 */
function testArea(string $path): array
{
    if (str_contains(basename($path), 'example')) {
        return [7, 27];
    }

    return [200_000_000_000_000, 400_000_000_000_000];
}

/**
 * @return list<array{px: int, py: int, pz: int, vx: int, vy: int, vz: int}>
 */
function loadHailstones(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $stones = [];

    foreach ($lines as $line) {
        if (! preg_match('/^(-?\d+),\s*(-?\d+),\s*(-?\d+)\s*@\s*(-?\d+),\s*(-?\d+),\s*(-?\d+)$/', trim($line), $match)) {
            throw new RuntimeException("Unrecognised hailstone: {$line}");
        }

        $stones[] = [
            'px' => (int) $match[1],
            'py' => (int) $match[2],
            'pz' => (int) $match[3],
            'vx' => (int) $match[4],
            'vy' => (int) $match[5],
            'vz' => (int) $match[6],
        ];
    }

    return $stones;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
