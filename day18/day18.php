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

function	solve($area, $visualize, $time)
{
	$minutes = 0;
	echo "Initial state:\n";
	display($area, false);

	while ($minutes++ < $time) {
		if (tick($area) === false)
			break;
		echo $minutes.PHP_EOL;
		if ($visualize) {
			echo "After ".$minutes." minutes.\n";
			display($area, false);
			usleep(200000);
		}
	}
	echo "Final state:\n";
	return display($area, true);
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
		echo "Solving...\n";
		$start = microtime(true);

		// Making sense of input.
		$input = digest_input($input);
		$result = solve($input, $visualize_result, 10);

		echo "There are \e[32m".$result["woods"]."\e[0m acres covered with trees and \e[32m".$result["open_fields"]."\e[0m acres of open field.\n";
		echo "Also there are \e[32m".$result["lumberyards"]."\e[0m lumberyards.\n";
		echo "Total resource value is: \e[32m". $result["lumberyards"] * $result["woods"] . "\e[0m.\n";

		$result = solve($input, $visualize_result, 1000000000);
		echo "After 1 Billion minutes, the resources value will be: \e[32m".$result["lumberyards"] * $result["woods"] . "\e[0m.\n";

	    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
