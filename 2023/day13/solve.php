<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 13: Point of Incidence.
 *
 * Part 1 example answer is 405. Part 2 example answer is 400.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$patterns = loadPatterns($path);

echo 'Advent of Code 2023 — Day 13: Point of Incidence', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($patterns), ' patterns', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$part1 = summarize($patterns, 0);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %6d score      %8s\n", 'Part 1  reflections', $part1, formatDuration($elapsed));

$startedAt = hrtime(true);
$part2 = summarize($patterns, 1);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-22s %6d score      %8s\n", 'Part 2  one smudge', $part2, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Sum reflection scores. $smudges is 0 for a perfect mirror, or 1 for the
 * single-cell difference that makes a different line valid.
 *
 * @param  list<list<string>>  $patterns
 */
function summarize(array $patterns, int $smudges): int
{
    $sum = 0;

    foreach ($patterns as $rows) {
        $sum += scorePattern($rows, $smudges);
    }

    return $sum;
}

/**
 * Vertical lines score the number of columns to the left of the mirror.
 * Horizontal lines score 100 times the number of rows above it.
 *
 * With one smudge, the line whose overlap differs by exactly one cell is
 * used, and the original perfect line is ignored.
 *
 * @param  list<string>  $rows
 */
function scorePattern(array $rows, int $smudges): int
{
    if ($smudges === 0) {
        return findLine($rows, 0)['score'];
    }

    $original = findLine($rows, 0);

    return findLine($rows, $smudges, $original['axis'], $original['index'])['score'];
}

/**
 * Find the unique reflection whose overlapping cells differ in exactly
 * $targetDiff positions. Skip the part-1 line when an axis and index are given.
 *
 * @param  list<string>  $rows
 * @return array{axis: string, index: int, score: int}
 */
function findLine(array $rows, int $targetDiff, ?string $skipAxis = null, ?int $skipIndex = null): array
{
    $height = count($rows);
    $width = strlen($rows[0]);

    for ($left = 1; $left < $width; $left++) {
        if ($skipAxis === 'v' && $skipIndex === $left) {
            continue;
        }

        if (columnDifferences($rows, $left) === $targetDiff) {
            return ['axis' => 'v', 'index' => $left, 'score' => $left];
        }
    }

    for ($above = 1; $above < $height; $above++) {
        if ($skipAxis === 'h' && $skipIndex === $above) {
            continue;
        }

        if (rowDifferences($rows, $above) === $targetDiff) {
            return ['axis' => 'h', 'index' => $above, 'score' => 100 * $above];
        }
    }

    throw new RuntimeException('No reflection with '.$targetDiff.' differing cells');
}

/**
 * Cells that disagree across a vertical mirror with $left columns to its left.
 *
 * @param  list<string>  $rows
 */
function columnDifferences(array $rows, int $left): int
{
    $width = strlen($rows[0]);
    $overlap = min($left, $width - $left);
    $diff = 0;

    foreach ($rows as $row) {
        for ($i = 0; $i < $overlap; $i++) {
            if ($row[$left - 1 - $i] !== $row[$left + $i]) {
                $diff++;
            }
        }
    }

    return $diff;
}

/**
 * Cells that disagree across a horizontal mirror with $above rows above it.
 *
 * @param  list<string>  $rows
 */
function rowDifferences(array $rows, int $above): int
{
    $height = count($rows);
    $width = strlen($rows[0]);
    $overlap = min($above, $height - $above);
    $diff = 0;

    for ($i = 0; $i < $overlap; $i++) {
        $upper = $rows[$above - 1 - $i];
        $lower = $rows[$above + $i];

        for ($col = 0; $col < $width; $col++) {
            if ($upper[$col] !== $lower[$col]) {
                $diff++;
            }
        }
    }

    return $diff;
}

/**
 * Patterns are separated by a blank line. Split the raw file so those
 * separators stay available as the split points.
 *
 * @return list<list<string>>
 */
function loadPatterns(string $path): array
{
    $raw = file_get_contents($path);

    if ($raw === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $raw = str_replace("\r\n", "\n", trim($raw));
    $blocks = preg_split("/\n\n/", $raw);

    if ($blocks === false) {
        throw new RuntimeException("Unable to split {$path}");
    }

    $patterns = [];

    foreach ($blocks as $block) {
        if ($block === '') {
            continue;
        }

        $patterns[] = explode("\n", $block);
    }

    return $patterns;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
