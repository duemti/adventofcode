<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: The sum of the risk levels of all low points on your heightmap \e[32m", part_one($data), "\e[0m\n";
// PART 2
echo "Part 2: The multiply together of the sizes of the three largest basins is \e[32m", part_two($data), "\e[0m\n";


function	part_one(array $map, array &$low_points = []): int
{
	foreach ($map as $y => $row)
	{
		foreach ($row as $x => $current)
		{
			foreach ([
				[$y - 1, $x],
				[$y + 1, $x],
				[$y, $x + 1],
				[$y, $x - 1]
			] as $i)
				if (isset($map[$i[0]][$i[1]]) && $map[$i[0]][$i[1]] <= $current)
					continue 2;
			$low_points[$y.".".$x] = $current;
		}
	}
	return array_sum($low_points) + count($low_points);
}

function	part_two(array $map): int
{
	$lowpoints = [];

	part_one($map, $lowpoints);
	foreach ($lowpoints as $key => $lp)
		$sizes[] = measure_bazin($map, [explode(".", $key)]);
	sort($sizes);
	return array_product(array_slice($sizes, -3));
}

function	measure_bazin(array $map, array $list): int
{
	$count = 0;

	while ($current = array_pop($list))
	{
		list($y, $x) = $current;

		foreach ([[$y - 1, $x], [$y + 1, $x], [$y, $x + 1], [$y, $x - 1]] as $i)
		{
			if (isset($map[$i[0]][$i[1]]) && $map[$i[0]][$i[1]] !== 9)
			{
				$map[ $i[0] ][ $i[1] ] = 9;
				array_push($list, [$i[0], $i[1]]);
				$count++;
			}
		}
	}
	return $count;
}

function	prep(string $data): array
{
	$scan = [];

	foreach (array_map('str_split', array_filter(explode("\n", $data))) as $row)
		$scan[] = array_map('intval', $row);
	return $scan;
}
