<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = array_map(
	'str_split',
	array_filter(
	explode("\n",
	file_get_contents($file)
)));

echo "Part 1: \e[32m", part_one($input), "\e[0m\n";
echo "Part 2: \e[32m", part_two($input), "\e[0m\n";

/**
 * Count all the tree's encountered at slove: right 3 & down 1.
 */
function	part_one(array $map, int $slopeX = 3, int $slopeY = 1): int
{
	// tree's encountered.
	$trees = 0;
	// $x is right.
	$x = $slopeX;
	// $y is down.
	$y = $slopeY;
	// width of scanned area.
	$width = count($map[0]);

	while ($y < count($map))
	{
		if (!isset($map[$y][$x]))
			$x -= $width;

		$location = $map[$y][$x];
		if ($location === "#")
			$trees++;
		$x += $slopeX;
		$y += $slopeY;
	}
	return $trees;
}

function	part_two(array $map): int
{
	$result = 1;

	foreach ([[1, 1], [3, 1], [5, 1], [7, 1], [1, 2]] as $slopes)
		$result *= part_one($map, $slopes[0], $slopes[1]);
	return $result;
}
