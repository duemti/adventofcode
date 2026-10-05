<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 4: Scratchcards.
 *
 * Part 1 example answer is 13. Part 2 example answer is 30.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$cards = loadCards($path);

echo 'Advent of Code 2023 — Day 4: Scratchcards', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($cards), ' cards', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$points = sumPoints($cards);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d points      %8s\n", 'Part 1  points', $points, formatDuration($elapsed));

$startedAt = hrtime(true);
$total = totalCards($cards);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d cards       %8s\n", 'Part 2  copies', $total, formatDuration($elapsed));
echo PHP_EOL;

/**
 * A card with n matches is worth 2^(n-1), or 0 when nothing matches.
 *
 * @param  list<int>  $matches
 */
function sumPoints(array $matches): int
{
    $sum = 0;

    foreach ($matches as $count) {
        if ($count > 0) {
            $sum += 1 << ($count - 1);
        }
    }

    return $sum;
}

/**
 * Each copy of a card with n matches adds one copy of each of the next n cards.
 *
 * @param  list<int>  $matches
 */
function totalCards(array $matches): int
{
    $copies = array_fill(0, count($matches), 1);

    foreach ($matches as $index => $count) {
        for ($offset = 1; $offset <= $count; $offset++) {
            $next = $index + $offset;

            if (! isset($copies[$next])) {
                break;
            }

            $copies[$next] += $copies[$index];
        }
    }

    return array_sum($copies);
}

/**
 * Match counts for each card, in file order.
 *
 * @return list<int>
 */
function loadCards(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $cards = [];

    foreach ($lines as $line) {
        if (! preg_match('/^Card\s+\d+:\s+(.+)\s+\|\s+(.+)$/', $line, $match)) {
            throw new RuntimeException("Unrecognised card: {$line}");
        }

        $winning = array_flip(preg_split('/\s+/', trim($match[1])) ?: []);
        $owned = preg_split('/\s+/', trim($match[2])) ?: [];
        $count = 0;

        foreach ($owned as $number) {
            if (isset($winning[$number])) {
                $count++;
            }
        }

        $cards[] = $count;
    }

    return $cards;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
