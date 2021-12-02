<?PHP

if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_filter(array_map('trim', $data));

// PART 1
$po = part_one($data);
echo "Part 1: The location would be at horizontal position of '",
	$po['horizontal'], "' and depth of '{$po['depth']}' forming the produce: \e[32m",
	$po['prod'], "\e[0m.\n";

// PART 2
$po = part_two($data);
echo "Part 2: After interpreting the commands the correct way,\n",
	"\tthe location would be at horizontal position of '",
	$po['horizontal'], "' and depth of '{$po['depth']}' forming the produce: \e[32m",
	$po['prod'], "\e[0m.\n";

function	part_one(array $data)
{
	$horizontal = 0;
	$depth = 0;

	foreach ($data as $row)
	{
		list($cmd, $x) = explode(' ', $row);

		switch ($cmd)
		{
			case "forward": $horizontal += intval($x); break;
			case "up": $depth -= intval($x); break;
			case "down": $depth += intval($x); break;
			default: echo "Warning: Unknown command '$cmd'.\n";
		}
	}
	return [
		'horizontal' => $horizontal,
		'depth' => $depth,
		'prod' => $horizontal * $depth
	];
}

function	part_two(array $data)
{
	$horizontal = 0;
	$depth = 0;
	$aim = 0;

	foreach ($data as $row)
	{
		list($cmd, $x) = explode(' ', $row);

		switch ($cmd)
		{
			case "forward":
				$horizontal += intval($x);
				$depth += intval($x) * $aim;
				break;
			case "up": $aim -= intval($x); break;
			case "down": $aim += intval($x); break;
			default: echo "Warning: Unknown command '$cmd'.\n";
		}
	}
	return [
		'horizontal' => $horizontal,
		'depth' => $depth,
		'prod' => $horizontal * $depth
	];
}
