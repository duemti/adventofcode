<?PHP
 
$data = init($argc, $argv);

echo "Part 1: After taking 10 steps there is \e[32m", part_one($data),
   	"\e[0m after subtracting the most common element by least common element.\n";
echo "Part 1: After taking 40 steps there is \e[32m", part_two($data),
   	"\e[0m after subtracting the most common element by least common element.\n";


function	part_one(array $data, int $steps = 10): int
{
	$polymer = $data['polymer'];
	$rules = $data['rules'];

	while ($steps--)
		$polymer = steps($polymer, $rules);

	foreach (array_keys($polymer) as $key)
	{
		$polymer[ $key[0] ] = isset($polymer[ $key[0] ])
			? ($polymer[ $key[0] ] + $polymer[ $key ])
			: $polymer[ $key ];
		unset($polymer[ $key ]);
	}
	$polymer[ $data['polymer-string'][-1] ]++;
	return max($polymer) - min($polymer);
}

function	steps(array $polymer, array $rules): array
{
	$n_polymer = array_fill_keys(array_keys($polymer), 0);

	foreach ($polymer as $pair => $count)
	{
		if (isset($rules[ $pair ]))
		{
			$n_polymer[ $pair[0] . $rules[ $pair ] ] += $count;
			$n_polymer[ $rules[ $pair ] . $pair[1] ] += $count;
		}
		else
			$n_polymer[ $pair ] = $count;
	}
	return $n_polymer;
}

function	part_two(array $data): int
{
	return part_one($data, 40);
}

function	init(int $argc, array $argv): array
{
	if ($argc !== 2)
		die("Error: Please provide input file.\n");

	$data = file_get_contents($argv[1]);
	if ($data === false)
		die("Error: Invalid file.\n");

	list($poly, $tmp) = array_filter(explode("\n\n", $data));
	$poly = str_split($poly);
	$polymer = [];
	$rules = [];

	foreach (array_filter(explode("\n", $tmp)) as $rul)
	{
		list($k, $v) = explode(" -> ", $rul);
		$rules[ $k ] = $v;
		$polymer[ $k ] = 0;
	}
	for ($i = 1; $i < count($poly); $i++)
		$polymer[ $poly[$i - 1] . $poly[$i] ]++;

	return [
		'polymer' => $polymer,
		'polymer-string' => implode($poly),
		'rules' => $rules
	];
}
