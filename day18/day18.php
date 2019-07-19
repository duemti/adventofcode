<?PHP

function	digest_input($input)
{
	return ;
}

function	solve($input)
{
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

		// Split the string into array of string's by newline.
		$input = explode("\n", $input);

		// Making sense of input.
		$input = digest_input($input);

		$result = solve($input, $visualize_result);

	    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
