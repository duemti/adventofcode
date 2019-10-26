<?PHP
require __DIR__ . '/vendor/autoload.php';

function	visit(&$region, $prev_region)
{
	$time = $prev_region['distance'] + 1;
	$equipped = $prev_region['equipped'];
	$equip = [];

	switch ($region['type']) {
		case 2: // narrow
			if (in_array("torch", $equipped))
				$equip[] = "torch";
			if (in_array("neither", $equipped))
				$equip[] = "neither";
			if (count($equipped) == 1 && $equipped[0] == "climbing-gear") {
				$equip[] = ($prev_region['type'] == 0) ? "torch" : "neither";
				$time += 7;
			}
			break;
		case 1: // wet
			if (in_array("climbing-gear", $equipped))
				$equip[] = "climbing-gear";
			if (in_array("neither", $equipped))
				$equip[] = "neither";
			if (count($equipped) == 1 && $equipped[0] == "torch") {
				$equip[] = ($prev_region['type'] == 0) ? "climbing-gear" : "neither";
				$time += 7;
			}
			break;
		case 0: // rocky
			if (in_array("torch", $equipped))
				$equip[] = "torch";
			if (in_array("climbing-gear", $equipped))
				$equip[] = "climbing-gear";
			if (count($equipped) == 1 && $equipped[0] == "neither") {
				$equip[] = ($prev_region['type'] == 1) ? "climbing-gear" : "torch";
				$time += 7;
			}
			break;
	}

	if ($time < $region['distance']) {
		$region['distance'] = $time;
		$region['equipped'] = $equip;
		return true;
	} elseif ($time == $region['distance'])
		$region['equipped'] = array_unique(array_merge($region['equipped'], $equip));
	return false;
}

function	heuristic($target, $coor)
{
	return (abs($coor['x'] - $target['x']) + abs($coor['y'] - $target['y']));
}

function	find_fastest_path(&$cave, $target, $depth)
{
	$frontier = new Ds\PriorityQueue();
	$cave[0][0]['distance'] = 0;
	$cave[0][0]['equipped'] = ["torch"];

	$frontier->push(['x' => 0, 'y' => 0], 0);
	while (!$frontier->isEmpty()) {
		$current = $frontier->pop();

		if ($current == $target)
			break;

		list('y' => $y, 'x' => $x) = $current;
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

			if (!$cave[$coor['y']][$coor['x']]['visited']) {
				if (visit($cave[$coor['y']][$coor['x']], $cave[$y][$x])) {
					$priority = (heuristic($target, $coor) + $cave[$coor['y']][$coor['x']]['distance']) * -1;
					$frontier->push($coor, $priority);
				}
			}
		}
		$cave[$y][$x]['visited'] = true;
	}

	$t = $cave[$target['y']][$target['x']];
	if (!in_array("torch", $t['equipped']))
		$t['distance'] += 7;
	return $t['distance'];
}

function	geo_index(array &$cave, int $x, int $y, array $target, int $depth)
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

function	detect_terrain_type(&$cave, $x, $y, $target, $depth)
{
			$geo_index = geo_index($cave, $x, $y, $target, $depth);
			$cave[$y][$x]['erosion_lvl'] = ($geo_index + $depth) % 20183;
			$cave[$y][$x]['type'] = $cave[$y][$x]['erosion_lvl'] % 3;
			$cave[$y][$x]['distance'] = INF;
			$cave[$y][$x]['visited'] = false;
			return $cave[$y][$x]['type'];
}

function	map_the_cave($depth, $target)
{
	$risk_lvl = 0;
	$cave = array_fill(0, $target['y'], array_fill(0, $target['x'], ['type' => '?']));

	for ($y = 0; $y <= $target['y']; $y++) {
		for ($x = 0; $x <= $target['x']; $x++) {
			$risk_lvl += detect_terrain_type($cave, $x, $y, $target, $depth);
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
	$path = find_fastest_path($cave['cave'], $target, $depth);

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
