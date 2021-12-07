<?PHP
// I applied the Cocke–Younger–Kasami algorithm 
// (alternatively called CYK, or CKY) 
// which is a parsing algorithm for context-free grammars.

require_once __DIR__."/test.php";

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";

// Run tests...
if (in_array("-t", $argv))
	run_tests();

if (FALSE === file_exists($file))
	die("Error: There is no input puzzle file or file doesn't exist.\n");
$puzzle = read(file_get_contents($file));

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle): int
{
	$count = 0;
	$rules = cnf($puzzle['rules']);

	foreach ($puzzle['msgs'] as $k => $msg)
	{
		$prgs = "$k/". count($puzzle['msgs']);
		echo $prgs;
		$count += validate($msg, $rules);
		echo str_repeat(chr(8), strlen($prgs)).
			str_repeat(" ", strlen($prgs)).
			str_repeat(chr(8), strlen($prgs));
	}
	return $count;
}

function	validate(array $msg, array $rules): int
{
	$grid_row = new \Ds\Vector;
	$grid = new \Ds\Vector;

	// Step 1
	foreach ($msg as $k => $ch)
		$grid_row->push(search_rules("\"$ch\"", $rules));
	$grid->push($grid_row);

	// Step 2
	for ($y = 1; $y < count($msg); $y++)
	{
		$grid_row = new \Ds\Vector;

		for ($x = 0; $x < count($msg) - $y; $x++)
			$grid_row->push(matches($x, $y, $grid, $rules));
		$grid->push($grid_row);
	}
	return in_array(0, $grid->last()->get(0)) ? 1 : 0;
}

// Chomsky normal form
function	cnf(array $rules): array
{
	// Step 1: i believe there is no '0' on right side.
	// Step 2: also there are no null's.
	// Step 3: Remove unit Productions
	for ($k = 0; $k < count($rules); $k++)
	{
		if (!isset($rules[$k]))
			continue;
		foreach (array_keys($rules[$k]) as $rhs)
		{
			$val = $rules[$k][$rhs];
			if (count($val) !== 1 || !is_numeric($val[0]))
				continue;
			array_splice($rules[$k], $rhs, 1);
			$rules[$k] = array_merge($rules[$k], $rules[ intval($val[0]) ]);
			$k = -1;
			break;
		}
	}
	// Step 4: Replace productions on right side where are more than 2.
	for ($k = 0; $k < count($rules); $k++)
	{
		if (!isset($rules[$k]))
			continue;
		foreach (array_keys($rules[$k]) as $rhs)
		{
			if (count($rules[$k][$rhs]) <= 2)
				continue;
			$pos = max(array_keys($rules)) + 1;
			$rules[$pos][] = array_slice($rules[$k][$rhs], 0, 2);
			$rules[$k][$rhs] = array_merge(["$pos"], array_slice($rules[$k][$rhs], 2));
			$k = -1;
			break;
		}
	}

	$cnf_rules = [];
	$salt = 0;
	foreach (array_keys($rules) as $pos)
		foreach (array_keys($rules[$pos]) as $pp)
			$cnf_rules[ implode(" ", $rules[$pos][$pp]) ][] = $pos;
	return $cnf_rules;
}

function	matches(int $x, int $y, \Ds\Vector $grid, array $rules): array
{
	$grid_point = [];
	$i = 0;
	$j = $y - 1;

	while ($i < $y)
	{
		$l_cell = $grid->get($i++)->get($x);
		$r_cell = $grid->get($j--)->get($x + $i);

		foreach ($l_cell as $lc)
			foreach ($r_cell as $rc)
				if (($res = search_rules("$lc $rc", $rules)))
					$grid_point = array_merge($grid_point, $res);
	}
	return $grid_point;
}

function	search_rules(string $str, array $rules): array
{
	return isset($rules[$str]) ? $rules[$str] : [];
}

function	part_two(array $puzzle): int
{
	$puzzle['rules'][8][] = [42, 8];
	$puzzle['rules'][11][] = [42, 11, 31];

	return part_one($puzzle);
}

function	read(string $file): array
{
	$puzz = array_filter(explode("\n\n", $file));
	return ['rules' => parse(array_filter(explode("\n", $puzz[0]))), 'msgs' => array_map('str_split', array_filter(explode("\n", $puzz[1])))];
}

function	parse(array $rules): array
{
	$new_r = [];

	foreach ($rules as $rule)
	{
		$m = [];
		preg_match("/^(\d+): (.+)$/", $rule, $m);
		foreach (array_filter(explode(" | ", $m[2])) as $r)
			$new_r[intval($m[1])][] = array_filter(explode(" ", $r));
	}
	return $new_r;
}
