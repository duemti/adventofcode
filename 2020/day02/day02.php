<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = file_get_contents($file);
$input = parse($input);

echo "Part 1: \e[32m", part_one($input), "\e[0m\n";
echo "Part 2: \e[32m", part_two($input), "\e[0m\n";

function	part_one(array $input): int
{
	$count = 0;

	foreach ($input as $password)
	{
		list(, $min, $max, $char, $pass) = $password;

		$occurs = substr_count($pass, $char);
		if ((int)$min <= $occurs && $occurs <= (int)$max)
			$count++;
	}
	return $count;
}

function	part_two(array $input): int
{
	$count = 0;

	foreach ($input as $password)
	{
		list(, $pos1, $pos2, $ch, $pass) = $password;
		$pos1 = intval($pos1) - 1;
		$pos2 = intval($pos2) - 1;

		if (($pass[$pos1] === $ch && $pass[$pos2] !== $ch)
			|| ($pass[$pos1] !== $ch && $pass[$pos2] === $ch))
			$count++;
	}
	return $count;
}

function	parse(string $input): array
{
	$matches = [];
	$pattern = "/(\d+)-(\d+)\s(\w):\s(\w+)/";

	preg_match_all($pattern, $input, $matches, PREG_SET_ORDER);
	return $matches;
}
