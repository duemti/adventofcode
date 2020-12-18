<?PHP

require_once __DIR__."/test.php";

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";

// Run tests...
if (in_array("-t", $argv))
	run_tests();

if (FALSE === file_exists($file))
	die("Error: There is no input puzzle file or file doesn't exist.\n");
$puzzle = read(file_get_contents($file));

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle): int
{
	$sum = 0;

	foreach ($puzzle as $expression)
	{
		$unused = 0;
		$sum += evaluate(str_split(str_replace(" ", "", $expression)), $unused);
	}
	return $sum;
}

function	evaluate(array $expr, int &$i, bool $add_prior = false): int
{
	$e = [];

	for (; $i < count($expr); $i++)
	{
		switch ($expr[$i]) {
			case "(":
				$i++;
				$e[] = evaluate($expr, $i, $add_prior);
				break;
			case ")":
				return calc($e, $add_prior);
			default:
				$e[] = $expr[$i];
		}
		$firsttime = false;
	}
	return calc($e, $add_prior);
}

function	calc(array $e, bool $priority): int
{
	if ($priority)
		foreach (["+", "*"] as $op)
			while (($i = array_search($op, $e)) !== false)
				calculate($e, $op, $i);
	else
		while (count($e) !== 1)
			calculate($e, $e[1], 1);
	return $e[0];
}

function	calculate(array &$e, string $op, int $i)
{
	$e[$i] = $op === "+" ? (intval($e[$i - 1]) + intval($e[$i + 1])) : (intval($e[$i - 1]) * intval($e[$i + 1]));
	unset($e[$i - 1]);
	unset($e[$i + 1]);
	$e = array_values($e);
}

function	part_two(array $puzzle): int
{
	$sum = 0;

	foreach ($puzzle as $expression)
	{
		$unused = 0;
		$sum += evaluate(str_split(str_replace(" ", "", $expression)), $unused, true);
	}
	return $sum;
}

function	read(string $file): array
{
	return array_filter(explode("\n", $file));
}
