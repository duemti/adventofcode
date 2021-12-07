<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = read($file);

$outlet = [];
echo "Part 1: \e[32m", part_one($input, $outlet), "\e[0m\n";
echo "Part 2: \e[32m", part_two($outlet), "\e[0m\n";

function	part_one(array $input, array &$outlet): int
{
	$outlet = [0];
	$diff_one = 0;
	$diff_three = 0;

	while (true)
	{
		$found = [];

		$last = max($outlet);
		foreach (array_keys($input) as $key)
		{
			$adapter = $input[$key];

			if ($adapter - $last < 1 && $adapter - $last > 3)
				continue;

			$found[] = $adapter;
			unset($input[$key]);
		}
		$outlet = array_merge($outlet, $found);
		if (empty($found))
		{
			$outlet[] = $last + 3;
			break;
		}
	}
	sort($outlet);
	for ($i = 1; $i < count($outlet); $i++)
	{
		$diff = $outlet[$i] - $outlet[$i - 1];

		if ($diff === 1)
			$diff_one++;
		elseif ($diff === 3)
			$diff_three++;
	}
	return $diff_one * $diff_three;
}

function	part_two(array $input): int
{
	$ways = array_fill(0, count($input), 0);

	sort($input);
	for ($i = 0; $i < count($input); $i++)
	{
		if ($i === 0)
			$ways[0] = 1;

		if ($i > 0 && $input[$i] - $input[$i - 1] <= 3)
			$ways[$i] += $ways[$i - 1];
		if ($i > 1 && $input[$i] - $input[$i - 2] <= 3)
			$ways[$i] += $ways[$i - 2];
		if ($i > 2 && $input[$i] - $input[$i - 3] <= 3)
			$ways[$i] += $ways[$i - 3];
	}
	return array_pop($ways);
}

function	read(string $file): array
{
	return array_filter(explode("\n", file_get_contents($file)));
}
