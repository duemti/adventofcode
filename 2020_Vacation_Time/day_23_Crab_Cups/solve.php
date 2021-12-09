<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_map('intval', str_split(trim($data)));

// PART 1
echo "Part 1: The labels after cup 1 are \e[32m", part_one($data), "\e[0m\n";

// PART 2
echo "Part 2: The the labels multiplied are: \e[32m", part_two($data), "\e[0m\n";


/**
 * Simple way.
 */
function	part_one(array $cups, int $moves = 100): string
{
	while ($moves--)
	{
		$pickup = array_splice($cups, 1, 3);
		$dest = select_dest($cups);
		array_splice($cups, $dest + 1, 0, $pickup);
		array_push($cups, array_shift($cups));
	}
	$one = array_search(1, $cups);
	return implode(array_slice($cups, $one + 1)) . implode(array_reverse(array_slice($cups, 0, $one)));
}

function	select_dest(array $cups): int
{
	$cc = $cups[0];

	while (--$cc >= min($cups))
		if (false !== ($i = array_search($cc, $cups, true)))
			return $i;
	return array_search(max($cups), $cups);
}

/**
 * Optimised Way.
 */
function	part_two(array $cups, int $moves = 10000000, int $quantity = 1000000): int
{
	$cups = array_merge($cups, range(max($cups) + 1, $quantity));
	$to = array_slice($cups, 1);
	$last = array_pop($cups);
	$cups = array_combine($cups, $to);
	$cups[$last] = key($cups);

	$curr = key($cups);
	while ($moves--)
	{
		$p1 = $cups[$curr];
		$p2 = $cups[$p1];
		$p3 = $cups[$p2];

		$dest = $curr - 1;
		while ($dest === $p1 || $dest === $p2 || $dest === $p3 || $dest === 0)
			$dest = --$dest < 1 ? $quantity : $dest;
		$tmp = $cups[$dest];
		$cups[$dest] = $p1;
		$cups[$curr] = $cups[$p3];
		$cups[$p3] = $tmp;
		$curr = $cups[$curr];
	}
	return $cups[1] * $cups[$cups[1]];
}
