<?PHP

if ($argc !== 3)
	exit("Usage:\n\t$argv[0] [input file] [steps]\n");

$input = array_filter(explode("\n", file_get_contents($argv[1])));
foreach ($input as $line)
{
	preg_match("<x=(-?\d+), y=(-?\d+), z=(-?\d+)>", $line, $matches);
	$moons[] = [
		'x' => intval($matches[1]),
		'y' => intval($matches[2]),
		'z' => intval($matches[3]),
		'vel' => ['x' => 0, 'y' => 0, 'z' => 0]
	];
}

$time = 0;
$prev_state = $moons;
$loop = [];
while (!isset($time_steps) || $time < intval($argv[2]))
{
	time_step($moons);
	$time++;

	if (!isset($time_steps) && set_loop($loop, $prev_state, $moons, $time))
		$time_steps = lcm(array_values($loop));
	if ($time === intval($argv[2]))
		$state = $moons;
}
$energy = 0;
foreach ($state as $m)
	$energy += (abs($m['x']) + abs($m['y']) + abs($m['z'])) * (abs($m['vel']['x']) + abs($m['vel']['y']) + abs($m['vel']['z']));
echo "Total energy after $time steps: \e[32m$energy\e[0m.\n";
echo "After \e[32m$time_steps\e[0m time steps, the moons states matches previous state.\n";

function	set_loop(array &$loop, array $prev_state, array $moons, int $time): bool
{
	foreach (['x', 'y', 'z'] as $d)
	{
		if (isset($loop[ $d ]))
			continue ;

		foreach ($moons as $id => $m)
			if ($prev_state[$id][ $d ] !== $m[ $d ] || $m['vel'][ $d ] !== 0)
				continue 2;
		$loop[ $d ] = $time;
	}

	if (count($loop) !== 3)
		return false;
	return true;
}

function	lcm(array $val): int
{
	// find gcd
	$n = array_shift($val);
	foreach ($val as $v)
		$n = $n * ($v / (gcd($v, $n)));
	return $n;
}

function	gcd(int $a, int $b): int
{
	return ($b === 0) ? $a : gcd($b, $a % $b);
}

function	time_step(array &$moons)
{
		// update velocity by applying gravity
		foreach (['x', 'y', 'z'] as $dim)
			gravity($moons, $dim);
		
		// update position by velocity
		foreach ([0, 1, 2, 3] as $id)
			foreach (['x', 'y', 'z'] as $dim)
				$moons[ $id ][ $dim ] += $moons[ $id ]['vel'][ $dim ];
}

function	gravity(array &$moons, string $dimension)
{
	$pos = array_column($moons, $dimension);

	foreach ([0, 1, 2, 3] as $l)
	{
		foreach ([0, 1, 2, 3] as $r)
		{
			$gravity = $pos[$l] - $pos[$r];
			if ($gravity === 0 || $r === $l)
				continue;

			$moons[$l]['vel'][$dimension] += $gravity < 0 ? 1 : -1;
		}
	}
}
