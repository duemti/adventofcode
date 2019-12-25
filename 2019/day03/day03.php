<?PHP
error_reporting(-1);

function	solve(array $input)
{
	$intersect = [];
	$red = map_path($input[0]);
	$blue = map_path($input[1]);

	foreach ($red as $y => $row)
	{
		foreach ($row as $x => $step)
		{
			if (isset($blue[$y][$x]))
			{
				$steps = $step + $blue[$y][$x];
				$cab_dist = abs($y) + abs($x);
				if ($cab_dist > 0 && (!isset($smallest_cab_dist) || $smallest_cab_dist[0] > $cab_dist))
					$smallest_cab_dist = [$cab_dist, $steps];
				if (!isset($fewest_steps) || $fewest_steps > $steps)
					$fewest_steps = $steps;
			}
		}
	}
	return isset($intersect) ? [$smallest_cab_dist, $fewest_steps] : "[the cables have not crossed]";
}

function	map_path(string $path): array
{
	$coordinates = [];
	$intersect = [];
	$x = 0;
	$y = 0;
	$steps = 0;

	foreach (explode(",", $path) as $direction)
	{
		$till = intval(substr($direction, 1));
		while (0 < $till--)
		{
			$steps++;
			move(substr($direction, 0, 1), $x, $y);
			$coordinates[$y][$x] = $steps;
		}
	}
	return $coordinates;
}

function	move($direction, &$x, &$y)
{
	switch ($direction)
	{
		case 'R':
			++$x;
			break;
		case 'L':
			--$x;
			break;
		case 'D':
			--$y;
			break;
		case 'U':
			++$y;
			break;
		default:
			die("Error: ". $direction);
	}
}

$input = file($argv[1]);
$result = solve($input);
echo "Part 1: \e[32m", $result[0][0], "\e[0m | ", $result[0][1]," steps.\n";
echo "Part 2: \e[32m", $result[1], "\e[0m steps.\n";
