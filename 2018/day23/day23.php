<?PHP
ini_set('memory_limit', '2G');
error_reporting(E_ALL);

function	prepare($input)
{
	$in = [];
	$regex = "/pos=<(-?\d+),(-?\d+),(-?\d+)>, r=(-?\d+)/";

	foreach (explode("\n", $input) as $token) {
		if (empty($token))
			continue;

		if (false === preg_match($regex, $token, $m))
			die("input error.\n\n");

		$in[] = [
			'pos' => [intval($m[1]), intval($m[2]), intval($m[3])],
			'radius' => intval($m[4])
		];
	}
	return $in;
}

function	detect_bots_in_range($bots, $bot)
{
	$count = 0;
	list($x1, $y1, $z1) = $bot['pos'];

	foreach ($bots as $b) {
		list($x2, $y2, $z2) = $b['pos'];
		$dist = abs($x2 - $x1) + abs($y2 - $y1) + abs($z2 - $z1);

		if ($dist <= $bot['radius']) {
			$count++;
		}
	}
	return $count;
}

function	solve_first($bots)
{
	foreach ($bots as $bot)
		if (!isset($strongest) || $bot['radius'] > $strongest['radius'])
			$strongest = $bot;

	return detect_bots_in_range($bots, $strongest);
}

function	solve_second($bots)
{
	$original = [];

	foreach ($bots as $bot) {
		$s = array_sum($bot['pos']);

		foreach ([$s - $bot['radius'], $s + $bot['radius']] as $pos) {
			if (isset($original[$pos]))
				$original[$pos] += 1;
			else
				$original[$pos] = 1;
		}
	}
	return array_search(max($original), $original);
}

function	solve($input)
{
	echo solve_first($input).PHP_EOL;
	echo solve_second($input).PHP_EOL;
}

$input = file_get_contents($argv[1]);
$input = prepare($input);
solve($input).PHP_EOL;
