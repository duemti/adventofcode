<?PHP

function	get_smallest_distance($cave, &$y, &$x)
{
	$y = -1;
	$x = -1;

	foreach ($cave as $dy => $row) {
		foreach ($row as $dx => $reg) {
			if (!$reg['visited'] && (!isset($distance) || $reg['distance'] < $distance)) {
				$distance = $reg['distance'];
				$y = $dy;
				$x = $dx;
			}
		}
	}
}

function	visit(&$region, $prev_region)
{
	$time = $prev_region['distance'] + 1;
	$equipped = $prev_region['equipped'];

	switch ($region['type']) {
		case 2: // narrow
			if (in_array("torch", $equipped))
				$equipped = "torch";
			elseif (in_array("neither", $equipped))
				$equipped = "neither";
			else {
				$equipped = ($prev_region['type'] == 0) ? "torch" : "neither";
				$time += 7;
			}
			break;
		case 1: // wet
			if (in_array("climbing-gear", $equipped))
				$equipped = "climbing-gear";
			elseif (in_array("neither", $equipped))
				$equipped = "neither";
			else {
				$equipped = ($prev_region['type'] == 0) ? "climbing-gear" : "neither";
				$time += 7;
			}
			break;
		case 0: // rocky
			if (in_array("torch", $equipped))
				$equipped = "torch";
			elseif (in_array("climbing-gear", $equipped))
				$equipped = "climbing-gear";
			else {
				$equipped = ($prev_region['type'] == 1) ? "climbing-gear" : "torch";
				$time += 7;
			}
			break;
	}

	if ($time <= $region['distance']) {
		$region['distance'] = $time;
		$region['equipped'][] = $equipped;
	}
}

function	find_fastest_path(&$cave, $target)
{
	$cave[0][0]['distance'] = 0;
	$cave[0][0]['equipped'] = ["torch"];
	$y = 0;
	$x = 0;

	while (1) {
		get_smallest_distance($cave, $y, $x);
		if ($y < 0)
			break;
		foreach ([
					[$y - 1, $x],	// ^
					[$y + 1, $x],	// v
					[$y, $x + 1],	// >
					[$y, $x - 1]	// <
				] as $coor) {
			if (!isset($cave[$coor[0]][$coor[1]]))
				continue ;
			$reg = &$cave[$coor[0]][$coor[1]];
			if (!$reg['visited'])
				visit($reg, $cave[$y][$x]);
		}
		$cave[$y][$x]['visited'] = true;
	}
	$t = $cave[$target['y']][$target['x']];
	print_R($t['equipped']);
	if (!in_array("torch", $t['equipped']))
		$t['distance'] += 7;
	echo "=> ",$t['distance'].PHP_EOL;
}

function	geo_index(array $cave, int $x, int $y, array $target)
{
	if (($x == 0 && $y == 0) || ($x == $target['x'] && $y == $target['y']))
		return 0;
	if ($y == 0)
		return ($x * 16807);
	if ($x == 0)
		return ($y * 48271);
	return ($cave[$y - 1][$x]['erosion_lvl'] * $cave[$y][$x - 1]['erosion_lvl']);
}

function	map_the_cave($depth, $target, $width, $height)
{
	$risk_lvl = 0;
	$cave = array_fill(0, $target['y'], array_fill(0, $target['x'], ['type' => '?']));

	for ($y = 0; $y <= $height; $y++) {
		for ($x = 0; $x <= $width; $x++) {
			$geo_index = geo_index($cave, $x, $y, $target);
			$cave[$y][$x]['erosion_lvl'] = ($geo_index + $depth) % 20183;
			$cave[$y][$x]['type'] = $cave[$y][$x]['erosion_lvl'] % 3;
			$cave[$y][$x]['distance'] = INF;
			$cave[$y][$x]['visited'] = false;

			$risk_lvl += $cave[$y][$x]['type'];
			echo ($cave[$y][$x]['type'] == 2) ? '|' : (($cave[$y][$x]['type']) ? '=' : '.');
		}
		echo PHP_EOL;
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

	$cave = map_the_cave($depth, $target, $target['x'] + 20, $target['y'] + 20);
	find_fastest_path($cave['cave'], $target);

	foreach ($cave['cave'] as $row) {
		foreach ($row as $r)
			echo $r['distance']," ";
		echo PHP_EOL;
	}

	return $cave['risk-lvl'];
}

// Main entry into the program.
if ($argc != 2) {
	echo "Usage: ".$argv[0]." [file]\n\n";
	echo "Options:\n";
	echo "Arguments:\n";
	echo "\tfile - File for input.\n";
	return ;
}

$file = $argv[1];

$input = file_get_contents($file);
if ($input === false)
	die("Failed to open ".$file."\n");

echo "Solving...\n";
// Solving...
$result = solve($input);
echo "Risk level is ", $result, PHP_EOL;
