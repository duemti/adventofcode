<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_map('intval', array_filter(array_map('trim', explode(",", $data))));

// PART 1
echo "Part 1: After 80 days there would be \e[32m",
	part_one($data), "\e[0m lanternfish.\n";

// PART 2
echo "Part 1: After 256 days there would be \e[32m",
	part_two($data), "\e[0m lanternfish.\n";


/**
 * Simple way.
 */
function	part_one(array $lanternfish, int $days = 80): int
{
	$tmp = $lanternfish;
	while (0 < $days--)
	{
		foreach (array_keys($lanternfish) as $id)
		{
			if (--$lanternfish[ $id ] < 0)
			{
				$lanternfish[ $id ] = 6;
				array_push($lanternfish, 8);
			}
		}

	}
	return count($lanternfish);
}

/**
 * Optimised Way.
 */
function	part_two(array $lanternfish, $days = 256): int
{
	$lf = array_count_values($lanternfish);

	while ($days--)
	{
		$new_lf = array_fill(0, 9, 0);

		foreach ($lf as $timer => $fish)
		{
			if ($timer - 1 < 0)
			{
				$new_lf[6] += $fish;
				$new_lf[8] += $fish;
			}
			else
				$new_lf[ $timer - 1 ] += $fish;
		}
		$lf = $new_lf;
	}
	return array_sum($lf);
}
