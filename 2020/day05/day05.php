<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");

$input = array_filter(explode("\n", file_get_contents($file)));

$seats_ids = [];
echo "Part 1: The highest seat ID is \e[32m", part_one($input, $seats_ids), "\e[0m.\n";
echo "Part 2: My seat id is \e[32m", part_two($seats_ids), "\e[0m.\n";

function	part_two(array $sids): int
{
	$my_sid = -1;

	sort($sids);
	for ($i = 1; $i < count($sids); $i++)
		if ($sids[$i] - 2 === $sids[$i - 1])
			$my_sid = $sids[$i] - 1;
	return $my_sid;
}

function	part_one(array $bpasses, array &$seats_ids): int
{
	$highest_seat_id = -1;

	foreach ($bpasses as $bpass)
	{
		$row = bsp(substr($bpass, 0, 7), 0, 127);
		$col = bsp(substr($bpass, 7, 3), 0, 7);

		$seat_id = $row * 8 + $col;
		if ($highest_seat_id < $seat_id)
			$highest_seat_id = $seat_id;
		$seats_ids[] = $seat_id;
	}
	return $highest_seat_id;
}

// binary space partitioning
function	bsp(string $inst, int $back, int $front): int
{
	foreach (str_split($inst) as $i)
	{
		if ($i === "B" || $i === "R")
			$back = get_half($back, $front, 1);
		else if ($i === "F" || $i === "L")
			$front = get_half($back, $front, 0);
	}
	return $front;
}

// find the middle number and return it.
function	get_half(int $b, int $f, int $which_half): int
{
	if ($f - $b === 1)
		return $which_half ? $f : $b;
	return (($b + $f) / 2) + $which_half;
}
