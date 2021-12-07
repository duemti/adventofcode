<?PHP

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$puzzle = read($file);

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle): int
{
	$mem = [];
	$mask = array_fill(0, 32, "X");

	foreach ($puzzle as $row)
	{
		if (isset($row['mask']))
			$mask = $row['mask'];
		else
			$mem[$row[0]] = mask($mask, to_bin($row[1]));
	}
	return array_sum($mem);
}

function	mask(array $mask, array $val): int
{
	foreach ($mask as $pos => $bit)
		if ($bit !== "X")
			$val[$pos] = $bit;
	return to_dec($val);
}

function	part_two(array $puzzle): int
{
	$mem = [];
	$mask = array_fill(0, 32, "X");

	foreach ($puzzle as $row)
	{
		if (isset($row['mask']))
			$mask = $row['mask'];
		else
			write_to($mem, $mask, to_bin($row[0]), $row[1]);
	}
	return array_sum($mem);
}

function	write_to(array &$mem, array $mask, array $at, int $val)
{
	$addresses = [$at];

	foreach ($mask as $pos => $bit)
	{
		if ($bit === "1")
			$addresses = set_bit($addresses, "1", $pos);
		elseif ($bit === "X")
			$addresses = array_merge(
				set_bit($addresses, "0", $pos),
				set_bit($addresses, "1", $pos));
	}

	foreach (array_map('to_dec', $addresses) as $addr)
		$mem[$addr] = $val;
}

function	set_bit(array $adrs, string $bit, int $pos): array
{
	foreach (array_keys($adrs) as $a)
		$adrs[$a][$pos] = $bit;
	return $adrs;
}

function	to_dec(array $val): int
{
	return intval(bindec(strrev(implode($val))));
}
function	to_bin(int $val): array
{
	return array_reverse(str_split(sprintf("%036b", $val)));
}

function	read(string $file): array
{
	$puzz = [];

	foreach (array_filter(explode("\n", file_get_contents($file))) as $line)
	{
		$m = [];
		preg_match("/^(?:mem\[(\d+)\] = (\d+)$|mask = (\w+)$)/", $line, $m);
		$puzz[] = (isset($m[3])) ?
			['mask' => array_reverse(str_split($m[3]))] :
			[intval($m[1]), intval($m[2])];
	}
	return $puzz;
}
