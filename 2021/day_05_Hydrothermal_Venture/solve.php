<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prepare_data($data);

// PART 1
echo "Part 1: There are \e[32m", part_one($data),
	"\e[0m data points that at least two lines of hydrothermal vents overlap.\n";

// PART 2
echo "Part 1: (Considering the diagonal lines) There are \e[32m", part_two($data),
	"\e[0m data points that at least two lines of hydrothermal vents overlap.\n";


function	part_one(array $data, bool $diagonal = false)
{
	$map = [];

	foreach ($data as $pts)
	{
		if ($pts['x1'] === $pts['x2'])
			foreach (range($pts['y1'], $pts['y2']) as $y)
				mark($map, $pts['x1'], $y);
		elseif ($pts['y1'] === $pts['y2'])
			foreach (range($pts['x1'], $pts['x2']) as $x)
				mark($map, $x, $pts['y1']);
		elseif ($diagonal)
		{
			foreach (array_combine(
					range($pts['x1'], $pts['x2']),
					range($pts['y1'], $pts['y2']),)
				as $x => $y
			) {
				mark($map, $x, $y);
			}
		}
	}
	return count(array_diff($map, [1]));
}

function	mark(array &$map, int $x, int $y)
{
	if (isset($map[ "$x.$y" ]))
		$map[ "$x.$y" ] += 1;
	else
		$map[ "$x.$y" ] = 1;
}

function	part_two(array $data)
{
	return part_one($data, true);
}

function	prepare_data(string $data): array
{
	foreach (array_filter(explode("\n", $data)) as $row)
	{
		preg_match("/(\d+),(\d+) -> (\d+),(\d+)/", $row, $mat);

		$pd[] = [
			'x1' => intval($mat[1]),
			'y1' => intval($mat[2]),
			'x2' => intval($mat[3]),
			'y2' => intval($mat[4]),
		];
	}
	return $pd ?: [];
}
