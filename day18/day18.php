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

function	display($area)
{
	foreach ($area as $row) {
		foreach ($row as $acre) {

			switch ($acre) {
				case "#":
					echo "\e[90m";
					break;
				case "|":
					echo "\e[32m";
					break;
				default:
					echo "\e[94m";
			}
			echo $acre . "\e[0m";
		}
		echo PHP_EOL;
	}
	echo "Legend:\n\t\e[90m#\e[0m - Lumberyard.\n\t\e[32m|\e[0m - Tree's.\n\t\e[94m.\e[0m - Open field.\n\n";
}

function	solve($area)
{
	display($area);
	return ;
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

		$result = solve($input, $visualize_result);

	    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
