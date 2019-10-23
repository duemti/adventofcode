<?PHP

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

function	map_the_cave($depth, $target)
{
	$risk_lvl = 0;
	$cave = array_fill(0, $target['y'], array_fill(0, $target['x'], ['type' => '?']));

	for ($y = 0; $y <= $target['y']; $y++) {
		for ($x = 0; $x <= $target['x']; $x++) {
			$cave[$y][$x]['geo_index'] = geo_index($cave, $x, $y, $target);
			$cave[$y][$x]['erosion_lvl'] = ($cave[$y][$x]['geo_index'] + $depth) % 20183;
			$cave[$y][$x]['type'] = $cave[$y][$x]['erosion_lvl'] % 3;

			$risk_lvl += $cave[$y][$x]['type'];
			echo ($cave[$y][$x]['type'] == 2) ? '|' : (($cave[$y][$x]['type']) ? '=' : '.');
		}
		echo PHP_EOL;
	}
	return $risk_lvl;
}

function	solve($input)
{
	$regex = "/depth: (\d+)\ntarget: (\d+),(\d+)/";
	$match = [];

	if (! preg_match($regex, $input, $match))
		die("Invalid input.");
	$depth = intval($match[1]);
	$target = ['x' => intval($match[2]), 'y' => intval($match[3])];

	echo $depth, PHP_EOL;
	return map_the_cave($depth, $target);
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
