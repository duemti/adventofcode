<?PHP

function find_range($clay)
{
	foreach ($clay as $sq) {
		if (!isset($maxy) || $maxy < $sq['y'])
			$maxy = $sq['y'];
		if (!isset($miny) || $miny > $sq['y'])
			$miny = $sq['y'];
		if (!isset($maxx) || $maxx < $sq['x'])
			$maxx = $sq['x'];
		if (!isset($minx) || $minx > $sq['x'])
			$minx = $sq['x'];
	}
	return ['y' => --$miny, 'Y' => ++$maxy, 'x' => --$minx, 'X' => ++$maxx];
}

function first_part($clay_veins, $range)
{
	$spring = ['x' => 500, 'y' => 0];
	$water = [];
	$height = 0;

	while ($height < $range['Y']) {

		$water = ['x' => $spring['x']++, 'y' => $height];

		for ($i = $range['x']; $i < $range['X']; $i++) {

			$square = ['x' => $i, 'y' => $height];

			echo (in_array($square, $clay_veins)) ? "#" : (($water == $square) ? "~" : ".");
		}
		echo PHP_EOL;

		$spring = $water;
		$height++;
	}
}

function digest_input($input)
{
	$result = [];
	$regex = "/(x|y)=(\d+), (x|y)=(\d+)..(\d+)/";

	foreach ($input as $row) {
		if (!$row)
			continue;

		if (preg_match($regex, $row, $match)) {
			foreach (range($match[4], $match[5]) as $coor) {
				$result[] = [$match[1] => $match[2], $match[3] => $coor];
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

	$input = digest_input(explode("\n", $input));
	$result = first_part($input, find_range($input));

    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
