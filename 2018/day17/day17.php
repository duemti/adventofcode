<?PHP

function find_range($clay_veins)
{
	$maxy = 1;

	foreach ($clay_veins as $cv) {
		if (!isset($minx) || $cv['x'] < $minx)
			$minx = $cv['x'];

		if (!isset($maxx) || $cv['x'] > $maxx)
			$maxx = $cv['x'];

		if (!isset($miny) || $cv['y'] < $miny)
			$miny = $cv['y'];

		if ($cv['y'] > $maxy)
			$maxy = $cv['y'];
	}
	return ['miny' => $miny, 'maxy' => $maxy + 1, 'minx' => $minx - 1, 'maxx' => $maxx + 1];
}

function	first_part($clay_veins_coor, $visualize_result)
{
	$dimensions = find_range($clay_veins_coor);
	$grid = setup_the_grid($clay_veins_coor, $dimensions);
	$depth = 0;

	while ($depth < $dimensions['maxy']) {
		if (flow_one_row($grid[$depth], $grid[$depth + 1], $dimensions))
			$depth++;
		else
			$depth--;
	}
	return display($grid, $dimensions, $visualize_result);

}

// function to move the water horizontally.
//
// @var	$y	int	y coordinate of depth at which to move the water.
// @var	$x	int	minimum x coordinate for width.
// @var	$y	int	maximum y coordinate for width.
function	flow_one_row(&$row, &$next_row, $range)
{
	$water_moved = false;
	$go_up_again = false;

	for ($x = $range['minx']; $x <= $range['maxx']; $x++) {

		if ($row[$x] == "|" || $row[$x] == "+") {

			if ($next_row[$x] == "." || $next_row[$x] == "|") {
				$next_row[$x] = "|";
			}
			else {
				$left = check_left($row, $next_row, $x);
				$right = check_right($row, $next_row, $x);

				if ($left && $right) {
					while (++$left < $right)
						$row[$left] = "~";
					$go_up_again = true;
				}
			}
			$water_moved = true;
		}
	}
	return ($go_up_again) ? false : $water_moved;
}

function	check_left(&$row, &$next_row, $x)
{

	while ($row[--$x] == "." || $row[$x] == "|") {
		$row[$x] = "|";
		if ($next_row[$x] == "." || $next_row[$x] == "|") {
			$next_row[$x] = "|";
			return 0;
		}
	}
	return $x;
}

function	check_right(&$row, &$next_row, &$x)
{

	while ($row[++$x] == "." || $row[$x] == "|") {
		$row[$x] = "|";
		if ($next_row[$x] == "." || $next_row[$x] == "|") {
			$next_row[$x] = "|";
			return 0;
		}
	}
	return $x;
}

function	setup_the_grid($clay_veins_coordinates, $range)
{
	$grid = array_fill(0, $range['maxy'] + 1, array_fill($range['minx'], $range['maxx'], "."));

	// setting up the clay veins.
	foreach ($clay_veins_coordinates as $coor)
		$grid [$coor['y']] [$coor['x']] = "#";

	// setting up the spring.
	$grid[0][500] = "+";

	return $grid;
}

function	display($grid, $range, $visualize_result)
{
	$water_can_reach = 0;
	$settled_water = 0;

	for ($y = 0; $y <= $range['maxy']; $y++) {
		for ($x = $range['minx']; $x <= $range['maxx']; $x++) {
			$pin = $grid[$y][$x];

			if ($pin == "#")
				$color = "\e[90m";
			else if ($pin == "~" && $water_can_reach++) {
				$settled_water++;
				$color = "\e[34m";
			}
			else if ($pin == "|" || $pin == "+") {
				if ($y < $range['maxy'] && $y >= $range['miny'])
					$water_can_reach++;
				$color = "\e[94m";
			}
			else
				$color = "\e[33m";

			if ($visualize_result)
				echo $color . $pin . "\e[0m";
		}
		if ($visualize_result)
			echo PHP_EOL;
	}
	if ($visualize_result)
		echo PHP_EOL;
	return ["water_can_reach" => $water_can_reach, "settled_water" => $settled_water];
}

//
// The function digest the input in an array of arrays of the form:
// [
//    [
// 	    'x' => ?,
// 	    'y' => ?
// 	  ],
// 	  ...
// ]
//
function digest_input($input)
{
	$result = [];
	$regex = "/(x|y)=(\d+), (x|y)=(\d+)..(\d+)/";

	foreach ($input as $row) {
		if (!$row)
			continue;

		if (preg_match($regex, $row, $match)) {
			foreach (range($match[4], $match[5]) as $coor) {
				$result[] = [
					$match[1] => (int)$match[2],
					$match[3] => (int)$coor
				];
			}
		}
	}
	return $result;
}


if ($argc < 2 || $argc > 3) {
	echo "Usage: ".$argv[0]." [-o] [input file]\n";
	echo "Options:\n\t-o Visualize the result.";
} else {
	$visualize_result = false;
	$file = $argv[1];
	if ($argc == 3) {
		if ($argv[1] == "-o")
			$visualize_result = true;
		else
			die("Error: no such option.");
		$file = $argv[2];
	}
	$input = file_get_contents($file);
	if (!$input) {
    	die("Failed to open ".$argv[1]."\n");
	}
	else {
		echo "Part 1:\n";
		$start = microtime(true);

		// Split the string into array of string's by newline.
		$input = explode("\n", $input);

		// Making sense of input.
		$input = digest_input($input);

		$result = first_part($input, $visualize_result);
		echo "Water can reach \e[92m" . $result["water_can_reach"]. "\e[0m square meters.\n";
		echo "After the spring has drained only \e[92m" . $result["settled_water"] . "\e[0m s.m. of water remains trapped by clay veins.\n";

	    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
