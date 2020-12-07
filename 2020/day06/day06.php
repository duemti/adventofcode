<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = parse(file_get_contents($file));

echo "Part 1: The sum of 'yes' answer count is: \e[32m", part_one($input), "\e[0m.\n";
echo "Part 2: The sum of questions that everyone in the group said 'yes': \e[32m", part_two($input), "\e[0m.\n";

function	part_two(array $input): int
{
	$sum = 0;

	foreach ($input as $group)
	{
		$tmp = [];

		foreach ($group as $answers)
			foreach (array_filter(str_split($answers)) as $ans)
				$tmp[$ans] = isset($tmp[$ans]) ? $tmp[$ans] + 1 : 1;
		foreach ($tmp as $k => $v)
			if ($v === count($group))
				$sum++;
	}
	return $sum;
}

function	part_one(array $input): int
{
	$total_ans = 0;

	foreach ($input as $group)
	{
		$tmp = [];

		foreach ($group as $answers)
			foreach (array_filter(str_split($answers)) as $ans)
				$tmp[$ans] = true;
		$total_ans += count($tmp);
	}
	return $total_ans;
}

function	parse(string $input): array
{
	$in = [];

	foreach (explode("\n\n", $input) as $gr)
		$in[] = array_filter(explode("\n", $gr));
	return $in;
}
