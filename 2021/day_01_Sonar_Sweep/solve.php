<?PHP

if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_filter(array_map('trim', $data));

// PART 1
echo "Part 1: There are \e[32m",
	part_one($data),
	"\e[0m measurements larger than previous ones.\n";

// PART 2
echo "Part 2: There are \e[32m",
	part_two($data),
	"\e[0m sums that are larger than the previous sum.\n";


/**
 * Find number of measurements that are
 * larger than previous ones.
 */
function	part_one(array $data): int
{
	$count = 0;

	for ($i = 1; $i < count($data); $i++)
		if ($data[ $i - 1] < $data[ $i ])
			$count++;
	return $count;
}

/**
 * Find the number of times the three-measurement
 * sliding window increases.
 */
function	part_two(array $data): int
{
	$window = [];

	for ($i = 2; $i < count($data); $i++)
		$window[] = $data[ $i - 2 ] + $data[ $i - 1] + $data[ $i ];
	return part_one($window);;
}
