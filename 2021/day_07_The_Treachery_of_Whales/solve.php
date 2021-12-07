<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_map('intval', array_map('trim', explode(",", $data)));

// PART 1
$po = part_one($data);
echo "Part 1: To align at '{$po['pos']}' position, is needed \e[32m{$po['fuel']}\e[0m fuel.\n";

// PART 2
$pt = part_two($data);
echo "Part 1: To align at the correct position of '{$pt['pos']}', is needed \e[32m{$pt['fuel']}\e[0m fuel.\n";


/**
 * Simple. Sort ascending. Select the middle element.
 */
function	part_one(array $data): array
{
	$fuel = 0;

	sort($data);
	$position = $data[ (int)(count($data) / 2) ];
	foreach ($data as $sub)
		$fuel += abs($sub - $position);
	return [
		'pos' => $position,
		'fuel' => $fuel
	];
}

/**
 * Calculate the Average. Try the 2 integers of Averge. Return the smallest.
 */
function	part_two(array $data): array
{
	$fuel1 = 0;
	$fuel2 = 0;
	$position = array_sum($data) / count($data);

	foreach ($data as $sub)
	{
		$fuel1 += array_sum(range(0, abs($sub - floor($position))));
		$fuel2 += array_sum(range(0, abs($sub - ceil($position))));
	}
	return [
		'pos' => $position,
		'fuel' => ($fuel1 > $fuel2) ? $fuel2 : $fuel1
	];
}
