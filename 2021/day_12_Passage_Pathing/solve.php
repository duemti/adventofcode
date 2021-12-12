<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: There are \e[32m", part_one($data), "\e[0m paths that visit at most once a small cave.\n";

// PART 2
echo "Part 2: But allowing for only one cavern to be visited twice, then there are \e[32m", part_two($data), "\e[0m\n";

function	part_one(array $caves, bool $allow_twice = false): int
{
	$paths = [
		['start']
	];
	$count_paths = 0;

	while (!empty($paths))
	{
		$current = array_pop($paths);

		foreach ($caves[ $current[0] ] as $next_cavern)
		{
			if (ctype_lower($next_cavern) && in_array($next_cavern, $current))
			{
				if (false === $allow_twice || in_array("double", $current) || $next_cavern === "start")
					continue ;
				$paths[] = array_merge([$next_cavern, "double"], $current);
			}
			elseif ($next_cavern === "end")
				$count_paths++;
			else
				$paths[] = array_merge([$next_cavern], $current);
		}
	}
	return $count_paths;
}

function	part_two(array $caves): int
{
	return part_one($caves, true);
}

function	prep(string $data): array
{
	$caves = [];

	foreach (array_filter(explode("\n", $data)) as $row)
	{
		list($a, $b) = explode("-", $row);
		$caves[ $a ][] = $b;
		$caves[ $b ][] = $a;
	}	
	return $caves;
}
