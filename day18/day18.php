<?PHP

function	digest_input($input)
{
	$dinput = [];

	foreach (explode("\n", $input) as $row) {
		if ($row)
			array_push($dinput, str_split($row));
	}
	return $dinput;
}

function	display($area, $show_legend = true)
{
	$count = ["woods" => 0, "open_fields" => 0, "lumberyards" => 0];

	foreach ($area as $row) {
		foreach ($row as $acre) {

			switch ($acre) {
				case "#":
					echo "\e[90m";
					$count["lumberyards"]++;
					break;
				case "|":
					echo "\e[32m";
					$count["woods"]++;
					break;
				default:
					$count["open_fields"]++;
					echo "\e[94m";
			}
			echo $acre . "\e[0m";
		}
		echo PHP_EOL;
	}
	if ($show_legend)
		echo "Legend:\n\t\e[90m#\e[0m - Lumberyard.\n\t\e[32m|\e[0m - Tree's.\n\t\e[94m.\e[0m - Open field.\n";
	echo PHP_EOL;
	return $count;
}

function	detect($acre, &$surr)
{
	switch ($acre) {
		case "|":
			$surr["woods"] += 1;
			break;
		case "#":
			$surr["lumberyards"] += 1;
			break;
		case ".":
			$surr["openfields"] += 1;
			break;
	}
}

function	detect_surroundings($x, $y, $area)
{
	$surr = [
		"woods" => 0,
		"lumberyards" => 0,
		"openfields" => 0
	];

	foreach (array(
			[$x - 1, $y - 1],
			[$x, $y - 1],
			[$x + 1, $y - 1],
			[$x - 1, $y],
			[$x + 1, $y],
			[$x - 1, $y + 1],
			[$x, $y + 1],
			[$x + 1, $y + 1],
		) as $coor) {
		if (isset($area[$coor[1]][$coor[0]])) {
			detect($area[$coor[1]][$coor[0]], $surr);
		}
	}
	return $surr;
}

function	tick(&$area)
{
	$was_changes = false;
	$new_area = $area;

	for ($y = 0; $y < count($area); $y++) {
		for ($x = 0; $x < count($area[$y]); $x++) {

			$surr = detect_surroundings($x, $y, $area);

			switch ($area[$y][$x]) {
				case ".":
					if ($surr["woods"] >= 3) {
						$new_area[$y][$x] = "|";
						$was_changes = true;
					}
					break;
				case "|":
					if ($surr["lumberyards"] >= 3) {
						$new_area[$y][$x] = "#";
						$was_changes = true;
					}
					break;
				case "#":
					if ($surr['lumberyards'] == 0 || $surr["woods"] == 0) {
						$new_area[$y][$x] = ".";
						$was_changes = true;
					}
					break;
			}
		}
	}
	$area = $new_area;
	return $was_changes;
}

function	solve($area, $visualize, $time, $snapshot_at = 1000)
{
	$minutes = 0;
	echo "Initial state:\n";
	display($area, false);

	while ($minutes++ < $time) {
		if (tick($area) === false)
			break;

		// Detect if there is a repeatable pattern.
		if ($minutes == $snapshot_at) {
			$area_snapshot = json_encode($area);
		}
		else if (isset($area_snapshot)) {
			if ($area_snapshot === json_encode($area)) {
				echo "Found pattern between ".$snapshot_at." and ".$minutes.PHP_EOL;
				$add = $minutes - $snapshot_at;
				while ($minutes + $add < $time)
					$minutes += $add;
			}
		}

		if ($visualize) {
			echo "After ".$minutes." minutes.\n";
			display($area, false);
			usleep(200000);
		}
	}
	echo "Final state:\n";
	return display($area, true);
}


if ($argc < 2 || $argc > 5) {
	echo "Usage: ".$argv[0]." [-o] [input file] [minutes] [snapshot]\n";
	echo "Options:\n\t-o Visualize the result.\n";
	echo "Arguments:\n";
	echo "\tminutes - The number of minutes to run. (default 10).\n";
	echo "\tsnapshot- At which minute to take a snapshot of area to use for pattern detection. (default 1000)\n";
} else {
	$visualize_result = false;
	$minutes = 10;
	$snapshot_at = 1000;
	$arg = 1;

	if ($argv[$arg] == "-o") {
		$visualize_result = true;
		$arg++;
	}
	$file = $argv[$arg++];
	if (isset($argv[$arg]))
		$minutes = intval($argv[$arg++]);
	if (isset($argv[$arg]))
		$snapshot_at = intval($argv[$arg++]);


	$input = file_get_contents($file);
	if (!$input) {
    	die("Failed to open ".$file."\n");
	}
	else {
		echo "Solving...\n";
		$start = microtime(true);

		// Making sense of input.
		$input = digest_input($input);

		$result = solve($input, $visualize_result, $minutes, $snapshot_at);
		echo "There are \e[32m".$result["woods"]."\e[0m acres covered with trees, \e[32m".$result["open_fields"]."\e[0m acres of open field and \e[32m".$result["lumberyards"]."\e[0m lumberyards.\n";
		echo "After ".$minutes." minutes, the total resources value will be: \e[32m".$result["lumberyards"] * $result["woods"] . "\e[0m.\n";

	    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
