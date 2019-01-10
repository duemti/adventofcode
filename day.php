<?PHP


function digest_input($input)
{
	$result = [];

	$result = $input;
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

    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
