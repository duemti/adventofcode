<?PHP

if ($argc !== 3)
	die("Usage: ". $argv[0]. " [input file] [preamble]\n\n");
$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = read($file);

$invalid_number = part_one($input, intval($argv[2]));
echo "Part 1: \e[32m", $invalid_number, "\e[0m\n";
echo "Part 2: \e[32m", part_two($input, $invalid_number), "\e[0m\n";

function	part_one(array $input, int $preamble = 25): int
{
	$pos = $preamble;

	while (isset($input[$pos]))
	{
		$n = $input[$pos];

		$have = find_sum(array_slice($input, $pos - $preamble, $preamble), $n);
		if (!$have)
			return $n;
		$pos++;
	}
	return -1;
}

function	part_two(array $input, int $inv_num): int
{
	while (null !== ($start = array_shift($input)))
	{
		$sum = $start;
		foreach ($input as $key => $num)
		{
			$sum += $num;

			if ($sum === $inv_num)
			{
				$range = array_slice($input, 0, $key + 1);
				// putting value back in range.
				$range[] = $start;
				return min($range) + max($range);
			}
			elseif ($sum > $inv_num)
				break;
		}
	}
	return -1;
}

// find in array two numbers equal to a number.
function	find_sum(array $nums, int $eq): bool
{
	while (!empty($nu = array_pop($nums)))
		for ($i = 0; $i < count($nums); $i++)
			if ($nu + $nums[$i] === $eq)
				return true;
	return false;
}

function	read(string $file): array
{
	return array_map('intval', file($file));
}
