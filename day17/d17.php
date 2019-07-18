<?PHP

function find_range($clay_veins)
{
	$minx = 500;
	$maxx = 500;
	$maxy = 1;

	foreach ($clay_veins as $cv) {
		if ($cv['x'] < $minx)
			$minx = $cv['x'];

		if ($cv['x'] > $maxx)
			$maxx = $cv['x'];

		if (!isset($miny) || $cv['y'] < $miny)
			$miny = $cv['y'];

		if ($cv['y'] > $maxy)
			$maxy = $cv['y'];
	}
	return ['miny' => $miny, 'maxy' => $maxy + 1, 'minx' => $minx - 1, 'maxx' => $maxx + 1];
}

function	first_part($clay_veins_coor)
{
	$dimensions = find_range($clay_veins_coor);
	$grid = setup_the_grid($clay_veins_coor, $dimensions);

	display($grid, $dimensions);
}

function	setup_the_grid($clay_veins_coordinates, $range)
{
	$grid = array_fill(0, $range['maxy'], array_fill($range['minx'], $range['maxx'], "."));

	// setting up the clay veins.
	for ($y = 0; $y <= $range['maxy']; $y++) {
		for ($x = $range['minx']; $x <= $range['maxx']; $x++) {

			$grid[$y][$x] = (in_array(['x' => $x, 'y' => $y], $clay_veins_coordinates)) ? "#" : ".";
		}
	}

	// setting up the spring.
	$grid[0][500] = "+";

	return $grid;
}

function	display($grid, $range)
{
	for ($y = 0; $y <= $range['maxy']; $y++) {
		for ($x = $range['minx']; $x <= $range['maxx']; $x++) {
			$pin = $grid[$y][$x];

			if ($pin == "#")
				echo "\e[90m";
			else if ($pin == "+")
				echo "\e[94m";
			else
				echo "\e[33m";

			echo $pin . "\e[0m";
		}
		echo PHP_EOL;
	}
	echo PHP_EOL;
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


if ($argc != 2) {
  echo "Usage: ".$argv[0]." [input file]\n";
} else {
  $input = file_get_contents($argv[1]);
  if (!$input)
  {
    echo "Failed to open ".$argv[1]."\n";
  }
  else {
    // Part 1
    echo "Part 1:\n";
	$start = microtime(true);

	// Split the string into array of string's by newline.
	$input = explode("\n", $input);

	// Making sense of input.
	$input = digest_input($input);

	$result = first_part($input);
	echo "Water can reach \e[92m" . $result. "\e[0m square meters.\n";

    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    //echo "\nPart 2:\n";
    //$start = microtime(true);
    //echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
