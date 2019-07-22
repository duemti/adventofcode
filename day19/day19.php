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

function	solve($input)
{
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

	echo "Solving...\n";
	$start = microtime(true);

	// Making sense of input.
	$input = digest_input($input);

	$result = solve($input, $visualize_result, $minutes, $snapshot_at);

    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
