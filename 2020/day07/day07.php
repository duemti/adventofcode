<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = parse(array_filter(\explode("\n", file_get_contents($file))));

echo "Part 1: \e[32m", part_one($input), "\e[0m.\n";
echo "Part 2: \e[32m", part_two($input), "\e[0m.\n";

function	part_one(array $input): int
{
	$count = [];
	$rules = ["shiny gold"];

	while (!empty($rules))
	{
		$rule = array_pop($rules);

		foreach ($input as $bag => $content)
		{
			if (isset($content[$rule]) && !isset($count[$bag]))
			{
				$rules[] = $bag;
				$count[$bag] = true;
			}
		}
	}
	return count($count);
}

function	part_two(array $input): int
{
	$rules = [[
		'r' => "shiny gold",
		'q' => 1
	]];
	$count = 0;

	while (!empty($rules))
	{
		$rule = array_pop($rules);

		if (isset($input[$rule['r']]))
		{
			foreach ($input[$rule['r']] as $r => $q)
			{
				$rules[] = ['r' => $r, 'q' => $rule['q'] * $q];
				$count += ($q * $rule['q']);
			}
		}
	}
	return $count;
}

function	parse(array $input): array
{
	$pattern = "/^(.+)\sbags\scontain\s(.+)$/";
	$newi = [];

	foreach ($input as $rule)
	{
		$m1 = [];
		$m2 = [];

		preg_match($pattern, $rule, $m1);
		if (!isset($m1[2]))
			$newi[ $m1[1] ] = [0];
		else
		{
			preg_match_all("/(\d+)\s([\w+\s]+)\sbag/", $m1[2], $m2);
			$newi[ $m1[1] ] = array_combine($m2[2], $m2[1]);
		}
	}
	return $newi;
}
