<?PHP
require __DIR__ . '/vendor/autoload.php';
define('ROCKY', 0);
define('WET', 1);
define('NARROW', 2);
define('TORCH', 'torch');
define('GEAR', 'gear');
define('NEITHER', 'neither');

error_reporting(-1);

function	cost($current, $next)
{
	$gear = INF;
	$neither = INF;
	$torch = INF;

	switch ($next['type']) {
		case ROCKY:
			switch ($current['type']) {
				case ROCKY:
					$gear = $current[GEAR] + 1;
					$torch = $current[TORCH] + 1;
					break;
				case WET:
					$gear = ($current[GEAR] < $current[NEITHER] + 7) ? ($current[GEAR] + 1) : ($current[NEITHER] + 8);
					$torch = $gear + 7;
					break;
				case NARROW:
					$torch = ($current[TORCH] > $current[NEITHER] + 7) ? ($current[NEITHER] + 8) : ($current[TORCH] + 1);
					$gear = $torch + 7;
					break;
			}
			break;
		case WET:
			switch ($current['type']) {
				case ROCKY:
					$gear = ($current[GEAR] > $current[TORCH] + 7) ? ($current[TORCH] + 8) : ($current[GEAR] + 1);
					$neither = $gear + 7;
					break;
				case WET:
					$gear = $current[GEAR] + 1;
					$neither = $current[NEITHER] + 1;
					break;
				case NARROW:
					$neither = ($current[NEITHER] > $current[TORCH] + 7) ? ($current[TORCH] + 8) : ($current[NEITHER] + 1);
					$gear = $neither + 7;
					break;
			}
			break;
		case NARROW:
			switch ($current['type']) {
				case ROCKY:
					$torch = ($current[GEAR] + 7 > $current[TORCH]) ? ($current[TORCH] + 1) : ($current[GEAR] + 8);
					$neither = $torch + 7;
					break;
				case WET:
					$neither = ($current[GEAR] + 7 > $current[NEITHER]) ? ($current[NEITHER] + 1) : ($current[GEAR] + 8);
					$torch = $neither + 7;
					break;
				case NARROW:
					$torch = $current[TORCH] + 1;
					$neither = $current[NEITHER] + 1;
					break;
			}
			break;
	}
	return [GEAR => $gear, TORCH => $torch, NEITHER => $neither];
}

function	heuristic($target, $coor)
{
	return (abs($coor['x'] - $target['x']) + abs($coor['y'] - $target['y']));
}

function	find_fastest_path(&$cave, $start, $target, $depth)
{
	$frontier = new Ds\PriorityQueue();
	$cost_so_far = [];
	$cave[0][0][TORCH] = 0;
	$cave[0][0][GEAR] = 7;
	$cave[0][0][NEITHER] = INF;
	$cave[0][0]['distance'] = 0;

foreach ($cave as $row)
	$frontier->push($start, 0);
	while (!$frontier->isEmpty()) {
		$current = $frontier->pop();

		if ($current == $target)
			break;

		list('y' => $y, 'x' => $x) = $current;
		$current = &$cave[$y][$x];
		foreach ([
					['y' => $y - 1, 'x' => $x],	// ^
					['y' => $y + 1, 'x' => $x],	// v
					['y' => $y, 'x' => $x + 1],	// >
					['y' => $y, 'x' => $x - 1]	// <
				] as $coor) {

			if (!isset($cave[$coor['y']][$coor['x']])) {
				if ($coor['x'] < 0 || $coor['y'] < 0)
					continue;
				detect_terrain_type($cave, $coor['x'], $coor['y'], $target, $depth);
			}
			$next = &$cave[$coor['y']][$coor['x']];
			$new_cost = cost($current, $next);

			foreach ([GEAR, TORCH, NEITHER] as $tool) {
				if ($new_cost[$tool] < $next[$tool]) {
					$next[$tool] = $new_cost[$tool];
					$priority = (heuristic($target, $coor) + $new_cost[$tool]) * -1;
					$frontier->push($coor, $priority);
				}
			}
			unset($next);
		}
		unset($current);
	}
	return $cave[$target['y']][$target['x']][TORCH];
}

function	geo_index(array &$cave, int $x, int $y, array $target, int $depth): int
{
	if (($x == 0 && $y == 0) || ($x == $target['x'] && $y == $target['y']))
		return 0;
	if ($y == 0)
		return ($x * 16807);
	if ($x == 0)
		return ($y * 48271);
	if (!isset($cave[$y - 1][$x]))
		detect_terrain_type($cave, $x, $y - 1, $target, $depth);
	if (!isset($cave[$y][$x - 1]))
		detect_terrain_type($cave, $x - 1, $y, $target, $depth);
	return ($cave[$y - 1][$x]['erosion_lvl'] * $cave[$y][$x - 1]['erosion_lvl']);
}

function	detect_terrain_type(array &$cave, int $x, int $y, array $target, int $depth): void
{
			$geo_index = geo_index($cave, $x, $y, $target, $depth);
			$cave[$y][$x]['erosion_lvl'] = ($geo_index + $depth) % 20183;
			$cave[$y][$x]['type'] = $cave[$y][$x]['erosion_lvl'] % 3;
			$cave[$y][$x][TORCH] = INF;
			$cave[$y][$x][GEAR] = INF;
			$cave[$y][$x][NEITHER] = INF;
}

function	map_the_cave($depth, $target)
{
	$risk_lvl = 0;
	$cave = array_fill(0, $target['y'], array_fill(0, $target['x'], []));

	for ($y = 0; $y <= $target['y']; $y++) {
		for ($x = 0; $x <= $target['x']; $x++) {
			detect_terrain_type($cave, $x, $y, $target, $depth);
			$risk_lvl += $cave[$y][$x]['type'];
		}
	}
	return ["risk-lvl" => $risk_lvl, "cave" => $cave];
}

function	solve($input)
{
	$regex = "/depth: (\d+)\ntarget: (\d+),(\d+)/";
	$match = [];

	if (! preg_match($regex, $input, $match))
		die("Invalid input.");
	$depth = intval($match[1]);
	$target = ['x' => intval($match[2]), 'y' => intval($match[3])];

	$cave = map_the_cave($depth, $target);
	$start = ['y' => 0, 'x' => 0];
	$path = find_fastest_path($cave['cave'], $start, $target, $depth);

	return ["risk-lvl" => $cave['risk-lvl'], "time" => $path];
}

// Main entry into the program.
if ($argc != 2) {
	echo "Usage: ".$argv[0]." [file]\n\n";
	echo "Options:\n";
	echo "Arguments:\n";
	echo "\tfile - File for input.\n";
	return 1;
}

$file = $argv[1];

$input = file_get_contents($file);
if ($input === false)
	die("Failed to open ".$file."\n");

echo "Solving...\n";
// Solving...
$result = solve($input);
echo "Risk level is ", $result['risk-lvl'], PHP_EOL;
echo "Minimum time ", $result['time'], PHP_EOL;
