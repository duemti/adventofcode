<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: There are \e[32m", part_one($data),
	"\e[0m dots after first fold instruction.\n";

// PART 2
echo "Part 2: After completing all folding instrucitons:\n",
	part_two($data), PHP_EOL;

function	part_one(array $data): int
{
	list('coors' => $coors, 'instr' => $inst) = $data;

	return array_sum(array_map('count', fold($coors, $inst[0])));
}

function	fold(array $coors, array $inst): array
{
	list($dir, $at) = $inst;

	foreach (array_keys($coors) as $y)
	{
		if ($dir === "y")
		{
			if ($y < $at)
				continue ;

			foreach ($coors[ $y ] as $x => $dot)
				$coors[ (2 * $at - $y) ][$x] = $dot;
			unset($coors[$y]);
		}
		else
		{
			foreach ($coors[ $y ] as $x => $dot)
			{
				if ($x < $at)
					continue ;
				$coors[$y][ (2 * $at - $x) ] = $dot;
				unset($coors[ $y ][ $x ]);
			}
		}
	}
	return $coors;
}

function	part_two(array $data)
{
	list('coors' => $coors, 'instr' => $inst) = $data;

	foreach ($inst as $in)
		$coors = fold($coors, $in);

	foreach ($coors as $y)
		if (!isset($maxx) || $maxx > max(array_keys($y)))
			$maxx = max(array_keys($y));
	$maxy = max(array_keys($coors));

	// Print the code.
	for ($y = -1; $y <= $maxy + 1; $y++)
	{
		echo "\e[32m", implode(
			array_replace(
				array_fill_keys(range(-1, $maxx + 1), "\e[0m.\e[32m"),
				(isset($coors[ $y ]) ? $coors[ $y ] : [])
			)
		), "\e[0m\n";
	}
}

function	prep(string $data): array
{
	list($coors, $instr) = array_filter(explode("\n\n", $data));
	$ret = [];

	foreach (array_filter(explode("\n", $coors)) as $v)
	{
		list($x, $y) = explode(",", $v);
		$ret['coors'][ $y ][ $x ] = "#";
	}
	foreach (array_filter(explode("\n", $instr)) as $v)
		$ret['instr'][] = explode("=", substr($v, 11));
	return $ret;
}
