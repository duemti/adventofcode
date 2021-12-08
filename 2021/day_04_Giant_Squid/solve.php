<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prepare_data($data);

// PART 1
echo "Part 1: The final score would be \e[32m", part_one($data), "\e[0m\n";

// PART 2
echo "Part 2: The final loser score would be \e[32m", part_two($data), "\e[0m\n";

function	part_one(array $data): int
{
	foreach ($data['numbers'] as $number)
	{
		foreach ($data['boards'] as $id => &$board)
		{
			// Search and mark on board
			foreach ($board as $y => $row)
				if (FALSE !== ($x = array_search($number, $row, true)))
					$board[$y][$x] = "*";

			// Vertically Check if board is the winner
			for ($x = 0; $x < count($board[0]); $x++)
				if (1 === count(array_flip(array_column($board, $x))))
					return arr_sum($board) * $number;
			// Horizontally
			foreach ($board as $row)
				if (1 === count(array_flip($row)))
					return arr_sum($board) * $number;
		}
	}
	return -1;
}

function	arr_sum(array $board): int
{
	$sum = 0;

	foreach ($board as $row)
		foreach ($row as $num)
			if ($num !== "*")
				$sum += $num;
	return $sum;
}

function	part_two(array $data)
{
	foreach ($data['numbers'] as $number)
	{
		foreach (array_keys($data['boards']) as $id)
		{
			$board = &$data['boards'][$id];
			$unset = false;

			// Search and mark on board
			foreach ($board as $y => $row)
				if (FALSE !== ($x = array_search($number, $row, true)))
					$board[$y][$x] = "*";

			// Vertically Check if board is the winner
			for ($x = 0; $x < count($board[0]); $x++)
				if (1 === count(array_flip(array_column($board, $x))))
					$unset = true;
			// Horizontally
			foreach ($board as $row)
				if (1 === count(array_flip($row)))
					$unset = true;

			if ($unset)
			{
				if (count($data['boards']) === 1)
						return arr_sum($board) * $number;
				unset($data['boards'][ $id ]);
			}
		}
	}
	return -1;
}

function	prepare_data(string $data): array
{
	$data = explode("\n\n", $data);
	$numbers = explode(',', array_shift($data));
	$boards = [];

	foreach ($data as $i => $grid)
		foreach (array_filter(explode("\n", $grid)) as $row)
			$boards[$i][] = preg_split("/\s+/", trim($row));
	return [
		'numbers' => $numbers,
		'boards' => $boards
	];
}
