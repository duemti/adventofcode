<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 7: Camel Cards
 *
 * Part 1 example answer is 6440. Part 2 example answer is 5905.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$lines = loadLines($path);

echo 'Advent of Code 2023 — Day 7: Camel Cards', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($lines), ' lines', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$classic = totalWinnings($lines, false);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d winnings %8s\n", 'Part 1  cards', $classic, formatDuration($elapsed));

$startedAt = hrtime(true);
$jokers = totalWinnings($lines, true);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d winnings %8s\n", 'Part 2  jokers', $jokers, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<string>  $lines
 */
function totalWinnings(array $lines, bool $jokers): int
{
    $hands = [];

    foreach ($lines as $line) {
        [$cards, $bid] = explode(' ', $line);
        $hands[] = [
            'bid' => (int) $bid,
            'type' => handType($cards, $jokers),
            'strength' => cardStrengths($cards, $jokers),
        ];
    }

    usort($hands, function (array $left, array $right): int {
        return [$left['type'], $left['strength']] <=> [$right['type'], $right['strength']];
    });

    $winnings = 0;

    foreach ($hands as $rank => $hand) {
        $winnings += $hand['bid'] * ($rank + 1);
    }

    return $winnings;
}

function handType(string $cards, bool $jokers): int
{
    $counts = array_count_values(str_split($cards));

    if ($jokers && isset($counts['J'])) {
        $wild = $counts['J'];
        unset($counts['J']);

        if ($counts === []) {
            return 6;
        }

        arsort($counts);
        $counts[array_key_first($counts)] += $wild;
    }

    rsort($counts);

    return match (implode('', $counts)) {
        '5' => 6,
        '41' => 5,
        '32' => 4,
        '311' => 3,
        '221' => 2,
        '2111' => 1,
        '11111' => 0,
        default => throw new RuntimeException("Unknown hand {$cards}"),
    };
}

/**
 * @return list<int>
 */
function cardStrengths(string $cards, bool $jokers): array
{
    $order = $jokers ? 'J23456789TQKA' : '23456789TJQKA';
    $strengths = [];

    foreach (str_split($cards) as $card) {
        $strength = strpos($order, $card);

        if ($strength === false) {
            throw new RuntimeException("Unknown card {$card}");
        }

        $strengths[] = $strength;
    }

    return $strengths;
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
