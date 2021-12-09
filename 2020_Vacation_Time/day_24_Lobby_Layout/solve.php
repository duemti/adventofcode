<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: There are \e[32m",
	part_one($data), "\e[0m tiles black side up.\n";

// PART 2
echo "Part 2: After 100 days there would be \e[32m",
	part_two($data), "\e[0m black tiles.\n";


/**
 * Simple way.
 */
function	part_one(array $tiles, array &$floor = []): int
{
	$directions = ['e', 'se', 'sw', 'w', 'nw', 'ne'];
	$floor = [[[1]]];

	foreach ($tiles as $tile)
	{
		list($z, $y, $x) = [0, 0, 0];

		while (strlen($tile))
		{
			foreach ($directions as $dir)
			{
				if (strncmp($tile, $dir, strlen($dir)))
					continue ;
				$tile = substr_replace($tile, '', 0, strlen($dir));

				switch ($dir)
				{
					case 'e': $x--; $z++; break;
					case 'se': $x--; $y++; break;
					case 'sw': $y++; $z--; break;
					case 'w': $x++; $z--; break;
					case 'nw': $y--; $x++; break;
					case 'ne': $z++; $y--; break;
					default: die("ERROR: Unknown direction.\n");
				}
			}
		}
		// White = 1, and Black = -1
		if (isset($floor[$z][$y][$x]))
			$floor[$z][$y][$x] *= -1;
		else
			$floor[$z][$y][$x] = -1;
	}
	return count_tiles($floor);
}

function	count_tiles(array $tiles)
{
	// Count black facing tiles.
	$result = 0;

	foreach ($tiles as $z)
		foreach ($z as $y)
			foreach ($y as $x)
				if ($x === -1)
					$result++;
	return $result;
}

/**
 * Optimised Way.
 */
function	part_two(array $tiles, int $days = 100): int
{
	$neighbours = [
		[1, 0, -1], [0, 1, -1], [-1, 1, 0],
		[-1, 0, 1], [0, -1, 1], [1, -1, 0]
	];
	$floor = [];
	
	part_one($tiles, $floor);
	// A type of Conway's Life Game
	while ($days--)
	{
		add_white_tiles($floor);
		$next_floor = [];

		foreach ($floor as $z => $fz)
		{
			foreach ($fz as $y => $fy)
			{
				foreach ($fy as $x => $fx)
				{
					$count = [-1 => 0, 1 => 0];

					foreach ($neighbours as $n)
						if (isset($floor[ $z + $n[0] ][ $y + $n[1] ][ $x + $n[2] ]))
							$count[ $floor[ $z + $n[0] ][ $y + $n[1] ][ $x + $n[2] ] ]++;

					$next_floor[ $z ][ $y ][ $x ] = ($fx === -1 && ($count[-1] < 1 || $count[-1] > 2))
						? 1 : (($fx === 1 && $count[-1] === 2) ? -1 : $fx);
				}
			}
		}
		$floor = $next_floor;
	}
	return count_tiles($floor);
}

// Set white tiles around black tiles
function	add_white_tiles(array &$floor)
{
	$neighbours = [
		[1, 0, -1], [0, 1, -1], [-1, 1, 0],
		[-1, 0, 1], [0, -1, 1], [1, -1, 0]
	];

	foreach ($floor as $z => $fz)
		foreach ($fz as $y => $fy)
			foreach ($fy as $x => $fx)
				if ($fx === -1)
					foreach ($neighbours as $n)
						if (!isset($floor[ $z + $n[0] ][ $y + $n[1] ][ $x + $n[2] ]))
							$floor[ $z + $n[0] ][ $y + $n[1] ][ $x + $n[2] ] = 1;
}

function	prep(string $data): array
{
	return array_filter(explode("\n", $data));
}
