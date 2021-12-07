<?PHP
ini_set("memory_limit", "2G");
require_once __DIR__."/test.php";

// Usage: php -d extension=ds.so [file]

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";

// Run tests...
if (in_array("-t", $argv))
	run_tests();

if (FALSE === file_exists($file))
	die("Error: There is no input puzzle file or file doesn't exist.\n");
$puzzle = read(file_get_contents($file));

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "(second part takes a minute to finish!)\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle, int $max_turns = 2020): int
{
	return part_two($puzzle, 2020);
}

function	part_two(array $puzzle, int $max_turns = 30000000): int
{
	$map = new \Ds\Map();
	foreach ($puzzle as $t => $n)
		$map->put($n, [$t + 1, 0]);

	$last_turns = $map->last()->value;
	$number = $map->last()->key;
	for ($turn = $last_turns[0] + 1; $turn <= $max_turns; $turn++)
	{
		$number = $last_turns[1] ? ($last_turns[0] - $last_turns[1]) : 0;
		$last_turns = [$turn, ($map->hasKey($number) ? $map->get($number)[0] : 0)];

		$map->put($number, $last_turns);
	}
	return $number;
}

function	read(string $file): array
{
	return array_map('intval', explode(",", $file));
}
