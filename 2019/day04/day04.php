<?PHP
declare(strict_types = 1);

/**
 * Increase the number starting from right to left
 */
function	increase(array $digits, int $pos = 5): array
{
	if ($digits[$pos]++ === 9)
	{
		$digits = increase($digits, $pos - 1);
		$digits[$pos] = $digits[$pos - 1];
	}
	return $digits;
}

function	adjacent_digits_match(array $digits, int $part): bool
{
	return $part === 1 ? part_one_constraints($digits) : part_two_constraints($digits);
}

function	part_one_constraints(array $digits): bool
{
	for ($i = 1; $i < count($digits); $i++)
		if ($digits[$i - 1] === $digits[$i])
			return true;
	return false;
}

function	part_two_constraints(array $digits): bool
{
	$matches = [];

	for ($i = 1; $i < count($digits); $i++)
	{
		if ($digits[$i - 1] === $digits[$i])
		{
			$matches[$i - 1] = $digits[$i];
			$matches[$i] = $digits[$i];
		}
	}
	$matches = array_count_values($matches);
	sort($matches, SORT_NUMERIC);
	return (!empty($matches) && array_shift($matches) === 2) ? true : false;
}

function	solve(string $input, int $part = 1): int
{
	$input = explode("-", $input);
	$count = 0;
	
	$password = array_map('intval', str_split($input[0]));
	$max = intval($input[1]);

	for ($i = 1; $i < count($password); $i++)
	{
		if ($password[$i] < $password[$i - 1])
		{
			$password = increase($password);
			$i = 0;
		}
	}

	while (1)
	{
		if (intval(implode($password)) > $max)
			break ;
		if (adjacent_digits_match($password, $part))
			$count++;
		$password = increase($password);
	}
	return $count;
}

$input = file_get_contents($argv[1]);
echo "Part 1: There are \e[32m", solve($input), "\e[0m passwords that meets the criteria.\n";
echo "Part 2: There are \e[32m", solve($input, 2), "\e[0m passwards that meets the new criteria.\n";
