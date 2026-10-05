<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 2: Cube Conundrum.
 *
 * Part 1 example answer is 8. Part 2 example answer is 2286.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$games = loadGames($path);

echo 'Advent of Code 2023 — Day 2: Cube Conundrum', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($games), ' games', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$possible = sumPossibleGames($games, 12, 13, 14);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d game ids    %8s\n", 'Part 1  possible', $possible, formatDuration($elapsed));

$startedAt = hrtime(true);
$power = sumPowers($games);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d power       %8s\n", 'Part 2  minimum set', $power, formatDuration($elapsed));
echo PHP_EOL;

/**
 * @param  list<array{id: int, red: int, green: int, blue: int}>  $games
 */
function sumPossibleGames(array $games, int $red, int $green, int $blue): int
{
    $sum = 0;

    foreach ($games as $game) {
        if ($game['red'] <= $red && $game['green'] <= $green && $game['blue'] <= $blue) {
            $sum += $game['id'];
        }
    }

    return $sum;
}

/**
 * @param  list<array{id: int, red: int, green: int, blue: int}>  $games
 */
function sumPowers(array $games): int
{
    $sum = 0;

    foreach ($games as $game) {
        $sum += $game['red'] * $game['green'] * $game['blue'];
    }

    return $sum;
}

/**
 * Each game keeps the largest count seen for each color.
 *
 * @return list<array{id: int, red: int, green: int, blue: int}>
 */
function loadGames(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $games = [];

    foreach ($lines as $line) {
        if (! preg_match('/^Game (\d+): (.+)$/', $line, $match)) {
            throw new RuntimeException("Unrecognised game: {$line}");
        }

        $counts = ['red' => 0, 'green' => 0, 'blue' => 0];

        preg_match_all('/(\d+) (red|green|blue)/', $match[2], $cubes, PREG_SET_ORDER);

        foreach ($cubes as $cube) {
            $counts[$cube[2]] = max($counts[$cube[2]], (int) $cube[1]);
        }

        $games[] = [
            'id' => (int) $match[1],
            'red' => $counts['red'],
            'green' => $counts['green'],
            'blue' => $counts['blue'],
        ];
    }

    return $games;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
