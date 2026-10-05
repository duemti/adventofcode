<?php

declare(strict_types=1);

/**
 * Advent of Code 2023, Day 20: Pulse Propagation.
 *
 * Part 1 example answer is 32000000. Part 2 has no example because rx is absent.
 */

$path = $argv[1] ?? __DIR__.'/puzzle-input.txt';
$modules = loadModules($path);

echo 'Advent of Code 2023 — Day 20: Pulse Propagation', PHP_EOL;
echo 'Input: ', basename($path), '   ', count($modules), ' modules', PHP_EOL, PHP_EOL;

$startedAt = hrtime(true);
$product = pulseProduct($modules, 1000);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d pulse product %8s\n", 'Part 1  1000 presses', $product, formatDuration($elapsed));

$startedAt = hrtime(true);
$presses = pressesUntilRx($modules);
$elapsed = (hrtime(true) - $startedAt) / 1_000_000;

printf("  %-20s %6d presses       %8s\n", 'Part 2  rx low', $presses, formatDuration($elapsed));
echo PHP_EOL;

/**
 * Low pulses sent times high pulses sent after $presses button presses.
 *
 * Every pulse counts, including the button's low pulse to broadcaster.
 *
 * @param  array<string, array{kind: string, dests: list<string>, on: bool, memory: array<string, bool>}>  $modules
 */
function pulseProduct(array $modules, int $presses): int
{
    $low = 0;
    $high = 0;

    for ($i = 0; $i < $presses; $i++) {
        [$pressLow, $pressHigh] = pushButton($modules);
        $low += $pressLow;
        $high += $pressHigh;
    }

    return $low * $high;
}

/**
 * Fewest button presses until rx receives a single low pulse.
 *
 * The example has no rx, so this returns 0 without simulating. On the real
 * machine, one conjunction feeds rx and is itself fed by several conjunctions.
 * Each of those sends a high pulse on a fixed period. The answer is the LCM of
 * the press counts when each input first sends high. Simulation stops at
 * 20000 presses.
 *
 * @param  array<string, array{kind: string, dests: list<string>, on: bool, memory: array<string, bool>}>  $modules
 */
function pressesUntilRx(array $modules): int
{
    $feeders = [];

    foreach ($modules as $name => $module) {
        if (in_array('rx', $module['dests'], true)) {
            $feeders[] = $name;
        }
    }

    if ($feeders === []) {
        return 0;
    }

    if (count($feeders) !== 1) {
        throw new RuntimeException('Expected one module to output to rx');
    }

    $feeder = $feeders[0];
    $periods = [];

    foreach ($modules as $name => $module) {
        if (in_array($feeder, $module['dests'], true)) {
            $periods[$name] = null;
        }
    }

    if ($periods === []) {
        throw new RuntimeException("The module that feeds rx ({$feeder}) has no inputs");
    }

    for ($press = 1; $press <= 20_000; $press++) {
        pushButton($modules, function (string $from, string $to, bool $isHigh) use ($feeder, &$periods, $press): void {
            if ($to === $feeder && $isHigh && array_key_exists($from, $periods) && $periods[$from] === null) {
                $periods[$from] = $press;
            }
        });

        $complete = true;

        foreach ($periods as $period) {
            if ($period === null) {
                $complete = false;
                break;
            }
        }

        if ($complete) {
            return leastCommonMultiple(array_values($periods));
        }
    }

    throw new RuntimeException('Not every input of the module feeding rx sent a high pulse within 20000 button presses');
}

/**
 * Push the button once and process pulses in FIFO order.
 *
 * Flip-flops start off, ignore high pulses, and toggle on low. Conjunctions
 * remember the last pulse from each input (default low) and send low only when
 * every remembered input is high.
 *
 * @param  array<string, array{kind: string, dests: list<string>, on: bool, memory: array<string, bool>}>  $modules
 * @param  (callable(string, string, bool): void)|null  $watch
 * @return array{0: int, 1: int}
 */
function pushButton(array &$modules, ?callable $watch = null): array
{
    $low = 0;
    $high = 0;
    $queue = [['button', 'broadcaster', false]];
    $head = 0;

    for ($sent = 0; $head < count($queue); $sent++) {
        if ($sent >= 1_000_000) {
            throw new RuntimeException('Pulse queue did not drain');
        }

        [$from, $to, $isHigh] = $queue[$head];
        $head++;

        if ($isHigh) {
            $high++;
        } else {
            $low++;
        }

        if ($watch !== null) {
            $watch($from, $to, $isHigh);
        }

        if (!isset($modules[$to])) {
            continue;
        }

        $kind = $modules[$to]['kind'];

        if ($kind === 'broadcaster') {
            foreach ($modules[$to]['dests'] as $dest) {
                $queue[] = [$to, $dest, $isHigh];
            }

            continue;
        }

        if ($kind === 'flip') {
            if ($isHigh) {
                continue;
            }

            $modules[$to]['on'] = !$modules[$to]['on'];
            $sendHigh = $modules[$to]['on'];

            foreach ($modules[$to]['dests'] as $dest) {
                $queue[] = [$to, $dest, $sendHigh];
            }

            continue;
        }

        if ($kind === 'conj') {
            $modules[$to]['memory'][$from] = $isHigh;
            $sendHigh = false;

            foreach ($modules[$to]['memory'] as $rememberedHigh) {
                if ($rememberedHigh === false) {
                    $sendHigh = true;
                    break;
                }
            }

            foreach ($modules[$to]['dests'] as $dest) {
                $queue[] = [$to, $dest, $sendHigh];
            }
        }
    }

    return [$low, $high];
}

/**
 * @return array<string, array{kind: string, dests: list<string>, on: bool, memory: array<string, bool>}>
 */
function loadModules(string $path): array
{
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $modules = [];

    foreach ($lines as $line) {
        [$left, $right] = explode(' -> ', trim($line), 2);
        $dests = array_map(trim(...), explode(',', $right));

        if ($left === 'broadcaster') {
            $kind = 'broadcaster';
            $name = 'broadcaster';
        } elseif (str_starts_with($left, '%')) {
            $kind = 'flip';
            $name = substr($left, 1);
        } elseif (str_starts_with($left, '&')) {
            $kind = 'conj';
            $name = substr($left, 1);
        } else {
            throw new RuntimeException("Unknown module {$left}");
        }

        $modules[$name] = [
            'kind' => $kind,
            'dests' => $dests,
            'on' => false,
            'memory' => [],
        ];
    }

    foreach ($modules as $name => $module) {
        foreach ($module['dests'] as $dest) {
            if (isset($modules[$dest]) && $modules[$dest]['kind'] === 'conj') {
                $modules[$dest]['memory'][$name] = false;
            }
        }
    }

    return $modules;
}

/**
 * @param  list<int>  $values
 */
function leastCommonMultiple(array $values): int
{
    $result = 1;

    foreach ($values as $value) {
        $result = intdiv($result, gcd($result, $value)) * $value;
    }

    return $result;
}

function gcd(int $a, int $b): int
{
    for ($step = 0; $b !== 0; $step++) {
        if ($step >= 128) {
            throw new RuntimeException('gcd did not converge');
        }

        [$a, $b] = [$b, $a % $b];
    }

    return $a;
}

function formatDuration(float $milliseconds): string
{
    if ($milliseconds < 1000) {
        return number_format($milliseconds, 0).' ms';
    }

    return number_format($milliseconds / 1000, 2).' s';
}
