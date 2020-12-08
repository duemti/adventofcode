<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = read($file);

echo "Part 1: \e[32m", part_one($input), "\e[0m\n";
echo "Part 2: \e[32m", part_two($input), "\e[0m\n";

function	part_one(array $input): int
{
	return run($input);
}

function	run(array $program, int &$accumulator = 0, bool $dup_as_err = false): int
{
	$ipointer_history = [];
	$ipointer = 0;

	while (1)
	{
		if ($ipointer >= count($program))
			return 999;
		if ($ipointer < 0)
			break;
		$instr = $program[$ipointer];

		if (in_array($ipointer, $ipointer_history))
			return ($dup_as_err) ? -1 : $accumulator;

		$ipointer_history[] = $ipointer;
		switch ($instr[0]) {
			case "acc":
				$accumulator += intval($instr[1]);
				$ipointer++;
				break;
			case "jmp":
				$ipointer += intval($instr[1]);
				break;
			case "nop":
				$ipointer++;
				break;
			default:
				die("Error: No such instruction {$instr[0]}.\n");
		}
	}
	return -1;
}

function	part_two(array $input): int
{
	for ($i = 0; $i < count($input); $i++)
	{
		$accumulator = 0;
		$program = $input;

		if ($program[$i][0] === "nop")
			$program[$i][0] = "jmp";
		elseif ($program[$i][0] === "jmp")
			$program[$i][0] = "nop";

		if (0 < run($program, $accumulator, true))
			return $accumulator;
	}
	return -1;
}

function	read(string $file): array
{
	$input = [];

	foreach (array_filter(explode("\n", file_get_contents($file))) as $inst)
		$input[] = explode(" ", $inst);
	return $input;
}
