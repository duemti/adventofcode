<?PHP
// php -d extension=ds day17.php
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

function	part_one(array $puzzle, int $cycles = 6): int
{
	$null = [];

	// Delete inactive cubes, bc solution doesnt require them.
	prune($puzzle);
	$puzzle = [$puzzle];
	while ($cycles--)
	{
		$new_puzzle = [];
		$to_check = [];

		foreach ($puzzle as $z => $zval)
			foreach ($zval as $y => $yval)
				foreach ($yval as $x => $cube)
					set_3d_cube([$z, $y, $x], "#", $puzzle, $new_puzzle, $to_check);
		foreach (array_unique($to_check) as $cube)
			set_3d_cube(json_decode($cube), ".", $puzzle, $new_puzzle, $null);
		$puzzle = $new_puzzle;
	}
	$count = 0;
	foreach ($puzzle as $z)
			foreach ($z as $y)
				$count += count($y);
	return $count;
}

function	prune(array &$puzzle)
{
	foreach (array_keys($puzzle) as $y)
		foreach (array_keys($puzzle[$y]) as $x)
			if ($puzzle[$y][$x] === ".")
				unset($puzzle[$y][$x]);
}

function	get_neigh(int $x, int $y, int $z): array
{
	return [
		[$z - 1, $y - 1, $x - 1], [$z - 1, $y - 1, $x - 0], [$z - 1, $y - 1, $x + 1],
		[$z - 1, $y - 0, $x - 1], [$z - 1, $y - 0, $x - 0], [$z - 1, $y - 0, $x + 1],
		[$z - 1, $y + 1, $x - 1], [$z - 1, $y + 1, $x - 0], [$z - 1, $y + 1, $x + 1],

		[$z - 0, $y - 1, $x - 1], [$z - 0, $y - 1, $x - 0], [$z - 0, $y - 1, $x + 1],
		[$z - 0, $y - 0, $x - 1], /***********************/ [$z - 0, $y - 0, $x + 1],
		[$z - 0, $y + 1, $x - 1], [$z - 0, $y + 1, $x - 0], [$z - 0, $y + 1, $x + 1],

		[$z + 1, $y - 1, $x - 1], [$z + 1, $y - 1, $x - 0], [$z + 1, $y - 1, $x + 1],
		[$z + 1, $y - 0, $x - 1], [$z + 1, $y - 0, $x - 0], [$z + 1, $y - 0, $x + 1],
		[$z + 1, $y + 1, $x - 1], [$z + 1, $y + 1, $x - 0], [$z + 1, $y + 1, $x + 1]
	];
}

function	set_3d_cube(array $addr, string $cube, array $grid, array &$new_grid, array &$to_check)
{
	list($z, $y, $x) = $addr;
	$neighbours = get_neigh($x, $y, $z);
	$active = 0;

	foreach ($neighbours as $n)
	{
		if (isset($grid[ $n[0] ][ $n[1] ][ $n[2] ]))
			$active++;
		else
			$to_check[] = json_encode($n);
	}
	if (($cube === "#" && ($active === 2 || $active === 3)) ||
		($cube === "." && $active === 3))
		$new_grid[$z][$y][$x] = "#";
}

function	set_4d_cube(array $addr, string $cube, array $grid, array &$new_grid, \Ds\Set &$to_check)
{
	list($w, $z, $y, $x) = $addr;
	$neighbors = get_neigh($x, $y, $z);

	$new_neighbors = [];
	foreach ($neighbors as $neighb)
		foreach ([$w - 1, $w, $w + 1] as $wval)
			$new_neighbors[] = array_merge([$wval], $neighb);
	$new_neighbors[] = [$w - 1, $z, $y, $x];
	$new_neighbors[] = [$w + 1, $z, $y, $x];
	$neighbors = $new_neighbors;
	$active = 0;

	foreach ($neighbors as $n)
	{
		if (isset($grid[ $n[0] ][ $n[1] ][ $n[2] ][ $n[3] ]))
			$active++;
		else
			if ($cube === "#")
				$to_check->add(json_encode($n));
	}
	if (($cube === "#" && ($active === 2 || $active === 3)) ||
		($cube === "." && $active === 3))
		$new_grid[$w][$z][$y][$x] = "#";
}

function	part_two(array $puzzle, int $cycles = 6): int
{
	$null = new \Ds\Set();

	// Delete inactive cubes, bc solution doesnt require them.
	prune($puzzle);
	$puzzle = [1 => [1 => $puzzle]];
	$to_check = new \Ds\Set();

	while ($cycles--)
	{
		$new_puzzle = [];

		foreach (array_keys($puzzle) as $w)
			foreach (array_keys($puzzle[$w]) as $z)
				foreach (array_keys($puzzle[$w][$z]) as $y)
					foreach (array_keys($puzzle[$w][$z][$y]) as $x)
						set_4d_cube([$w, $z, $y, $x], "#", $puzzle, $new_puzzle, $to_check);

		for ($i = 0; $i < $to_check->count(); $i++)
			set_4d_cube(json_decode($to_check->get($i)), ".", $puzzle, $new_puzzle, $null);

		$to_check->clear();
		$puzzle = $new_puzzle;
	}

	$count = 0;
	foreach (array_keys($puzzle) as $w)
		foreach (array_keys($puzzle[$w]) as $z)
			foreach (array_keys($puzzle[$w][$z]) as $y)
				foreach (array_keys($puzzle[$w][$z][$y]) as $x)
					$count++;

	return $count;
}

function	read(string $file): array
{
	$puzz = [];

	foreach (array_filter(explode("\n", $file)) as $row)
		$puzz[] = str_split($row);
	return $puzz;
}
