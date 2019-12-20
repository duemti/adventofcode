<?PHP

function	part_one(array $masses): int
{
	$fuel = 0;

	foreach ($masses as $mass)
		$fuel += floor((int)$mass / 3) - 2;
	return $fuel;
}

function	part_two(array $masses): int
{
	$total_mass = 0;

	foreach ($masses as $mass)
	{
		$mass = floor((int)$mass / 3) - 2;
		while ($mass >= 0)
		{
			$total_mass += $mass;
			$mass = floor($mass / 3) - 2;
		}
	}
	return $total_mass;
}


$input = file($argv[1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "Required fuel: \e[32m", part_one($input), "\e[0m\n";
echo "Accountining for fuel mass also: \e[32m", part_two($input), "\e[0m\n";
