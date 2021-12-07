<?PHP

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";
if (FALSE === file_exists($file))
	die("Error: The is no puzzle file or file doesn't exist.\n");
$puzzle = read($file);

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle, int $seats_rule = 4, bool $infinite = false): int
{
	do
	{
		$action = false;
		$tmp = $puzzle;

		foreach ($puzzle as $y => $col)
		{
			foreach ($col as $x => $seat)
			{
				$count = count_adj_taken($puzzle, $x, $y, $infinite);

				if ($seat === "L" && $count === 0)
					$tmp[$y][$x] = "#";
				elseif ($seat === "#" && $count >= $seats_rule)
					$tmp[$y][$x] = "L";
				else
					continue;
				$action = true;
			}
		}
		$puzzle = $tmp;
	} while ($action);
	return count_occupied_seats($puzzle);
}

function	count_occupied_seats(array $puzzle): int
{
	$count = 0;

	foreach ($puzzle as $row)
	{
		$val = array_count_values($row);

		if (isset($val["#"]))
			$count += $val["#"];
	}
	return $count;
}

function	part_two(array $puzzle): int
{
	return part_one($puzzle, 5, true);
}

function	count_adj_taken(array $puz, int $x, int $y, bool $infinite): int
{
	$taken = 0;

	// Moving clockwise.
	foreach ([
			[-1, -1], [-1, +0], [-1, 1],
			[+0, -1], /*******/ [+0, 1],
			[+1, -1], [+1, +0], [+1, 1],
		] as $seat
	) {
		$xx = $x;
		$yy = $y;

		do {
			$xx += $seat[0];
			$yy += $seat[1];

			if (isset($puz[$yy][$xx]))
				if ($puz[$yy][$xx] === "#")
				$taken++;
			elseif ($puz[$yy][$xx] === ".")
				continue;
			break;
		} while ($infinite);
	}
	return $taken;
}

function	read(string $file): array
{
	return array_map('str_split', array_filter(explode("\n", file_get_contents($file))));
}
