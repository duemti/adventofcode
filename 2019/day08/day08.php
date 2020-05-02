<?PHP
if ($argc !== 4)
	exit("Usage: ". $argv[0]. " [input file] [width] [height]\n");

$input = str_split(file_get_contents($argv[1]));

$wide = intval($argv[2]);
$tall = intval($argv[3]);

$layers = array_chunk($input, $wide * $tall);
foreach ($layers as $key => $layer)
{
	$count = array_count_values($layer);
	if (isset($count[0]) && !empty($count[0]) && (!isset($most_zeroes) || $most_zeroes[1][0] > $count[0]))
		$most_zeroes = [$key, $count];
}
$result = (!isset($most_zeroes[1][1]) || !isset($most_zeroes[1][2])) ? 0 : ($most_zeroes[1][1] * $most_zeroes[1][2]);
echo "The layer with most 0's is \e[32m", $most_zeroes[0], "\e[0m and number of digits of 1's & 2's multiplied is: \e[32m", $result, "\e[0m.\n";

$image = [];
foreach ($layers as $layer)
{
	foreach ($layer as $position => $pixel)
 		if (!isset($image[$position]) || $image[$position] === 2)
			$image[$position] = intval($pixel);
}

foreach ($image as $pos => $pixel)
{
	if ($pixel === 0)
		echo "\e[30m";
	else if ($pixel === 1)
		echo "\e[37;1m";
	echo $pixel."\e[0m";

	if ((1 + $pos) % $wide === 0)
		echo PHP_EOL;
}
echo PHP_EOL;
