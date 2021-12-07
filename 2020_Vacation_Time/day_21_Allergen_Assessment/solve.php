<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prepare_data($data);

// PART 1
echo "Part 1: There are \e[32m",
	part_one($data), "\e[0m ingredients that cannot possibly contain any of the allergens .\n";

// PART 2
echo "Part 2: The canonical ingredients list:  \e[32m",
	part_two($data), "\e[0m\n";


/**
 * Simple way.
 */
function	part_one(array $list): int
{
	$lst = [];
	$count = 0;

	foreach ($list['allergens'] as $alg)
	{
		$ci = common_ingredients($list['list'], $alg);
		$lst = empty($lst) ? $ci : array_merge($lst, $ci);
	}
	foreach ($list['list'] as $l)
		foreach ($l['ingredients'] as $ingredient)
			if (!in_array($ingredient, $lst))
				$count++;
	return $count;
}

function	common_ingredients(array $list, string $allergen): array
{
	foreach ($list as $l)
		if (in_array($allergen, $l['allergens']))
			$el = (!isset($el)) ? $l['ingredients'] : array_intersect($el, $l['ingredients']);
	return $el;
}

/**
 * Optimised Way.
 */
function	part_two(array $list): string
{
	$lst = [];
	$cdi = [];

	foreach ($list['allergens'] as $alg)
		$lst[ $alg ] = common_ingredients($list['list'], $alg);

	while (!empty($lst))
	{
		foreach (array_keys($lst) as $allergenic)
		{
			if (count($lst[ $allergenic ]) === 1)
				$cdi[ current($lst[ $allergenic ]) ] = retrieve($allergenic, current($lst[ $allergenic ]), $lst);
		}
	}
	asort($cdi, SORT_STRING);
	return implode(',', array_keys($cdi));
}

function	retrieve(string $allergenic, string $ingredient, array &$list): string
{
	foreach (array_keys($list) as $a)
		if (in_array($ingredient, $list[ $a ]))
			$list[ $a ] = array_diff($list[ $a ], [ $ingredient ]);
	unset($list[ $allergenic ]);
	return $allergenic;
}

function	prepare_data(string $data): array
{
	$allergens = [];
	$list = [];

	foreach (explode("\n", $data) as $pos => $row)
	{
		if (empty($row))
			continue ;
		$list[$pos]['allergens'] = [];

		if (preg_match("/\(contains (.+)\)/", $row, $matches))
		{
			foreach (explode(" ", $matches[1]) as $alrg)
				$list[$pos]['allergens'][] = trim($alrg, " ,");
			$allergens = array_merge($allergens, $list[$pos]['allergens']);
			$row = substr_replace($row, "", strlen($matches[1]) * -1 - 11);
		}
		$list[$pos]['ingredients'] = array_filter(explode(" ", $row));
	}
	return [
		'list' => $list,
		'allergens' => array_unique($allergens)
	];
}
