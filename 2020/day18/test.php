<?PHP

function	run_tests()
{
	// Loading test files...
	for ($n = 1; file_exists("test_$n.txt"); $n++)
		$tests[] = "test_$n.txt";

	if (isset($tests))
	{
		$loaded_tests = [];
		$pattern = "/^PUZZLE=\{(.+)\}\nPART_ONE=\{(\d+)\}\nPART_TWO=\{(\d+)\}$/";

		echo "Running tests...\n";
		foreach ($tests as $test)
		{
			$m = [];
			if (0 === preg_match($pattern, file_get_contents($test), $m))
			{
				echo "Error: Invalid test file: '$test'.\n";
				continue;
			}

			$res = part_one(read($m[1]));
			$msg = intval($m[2]) !== $res ?
				"\e[31m[ X ] - Expected '". $m[2]. "' got '$res' instead" :
				"\e[32m[ V ] - Success! got ". $m[2];
			echo "$test - Part 1:\t$msg.\e[0m\n";

			$res = part_two(read($m[1]));
			$msg = intval($m[3]) !== $res ?
				"\e[31m[ X ] - Expected '". $m[3]. "' got '". $res. "' instead" :
				"\e[32m[ V ] - Success! got ". $m[3];
			echo "$test - Part 2:\t$msg.\e[0m\n";
		}
	}
	else
		echo "Warning: There are no test files.\n";
}
