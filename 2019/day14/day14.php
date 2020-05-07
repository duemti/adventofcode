<?PHP

if ($argc !== 2)
	exit("Usage:\n\t$argv[0] [file]\n");

foreach (array_filter(explode("\n", file_get_contents($argv[1]))) as $line)
{
	preg_match_all("/(\d+) (\w+)/", $line, $matches, PREG_SET_ORDER);

	$prod = array_pop($matches);
	$react[ $prod[2] ]['q'] = intval($prod[1]);
	foreach ($matches as $m)
		$react[ $prod[2] ][ $m[2] ] = intval($m[1]);
}

solve($react);

function	solve(array $react)
{
	$excess = [];
	$ores = create_fuel($excess, $react, "FUEL", 1);
	echo "The minimum amount of ORE required to produce 1 FUEL is: \e[32m$ores\e[0m.\n";

	
	$trillion = 1000000000000;

	$left = 1;
	$right = $trillion;
	while ($left <= $right)
	{
		$fuel = floor($left + ($right - $left) / 2);
		$excess = [];
		$ores = create_fuel($excess, $react, "FUEL", $fuel);

		if ($ores == $trillion)
			break ;
		elseif ($ores > $trillion)
			$right = $fuel - 1;
		elseif ($ores < $trillion)
			$left = $fuel + 1;
	}
	echo "For 1 Trillion ORE's, the spacecraft can make \e[32m$fuel\e[0m FUEL.\n";
}

function	create_fuel(array &$elements_created, array $react, string $element, int $quantity_i_need): int
{
	$quant = 0;
	$increment = 1;
	$quantity_i_can_make = $react[ $element ][ 'q' ];

	if (! isset($elements_created[$element]))
		$elements_created[$element] = 0;

	if ($elements_created[$element] >= $quantity_i_need)
	{
		$elements_created[$element] -= $quantity_i_need;
		return 0;
	}
	else
	{
		$quantity_i_need -= $elements_created[$element];
		$elements_created[$element] = 0;
	}

	if ($quantity_i_need > $quantity_i_can_make * $increment)
		$increment = ceil($quantity_i_need / $quantity_i_can_make);
	$elements_created[$element] += ($quantity_i_can_make * $increment) - $quantity_i_need;

	foreach (array_keys($react[ $element ]) as $ingredient)
	{
		if ($ingredient === 'q')
			continue ;
		$ingredient_quantity_needed = $react[ $element ][ $ingredient ] * $increment;

		// Infinite supply of raw meterial ORE
		if ($ingredient === "ORE")
			$quant += $ingredient_quantity_needed;
		else
			$quant += create_fuel($elements_created, $react, $ingredient, $ingredient_quantity_needed);
	}
	return $quant;
}
