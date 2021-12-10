<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_map('intval', array_filter(explode("\n", ($data))));

// PART 1
echo "Part 1: The handshake is trying to establish \e[32m", part_one($data), "\e[0m encryption key.\n";

// PART 2
echo "Part 2: The canonical ingredients list:  \e[32m",
	part_two($data), "\e[0m\n";


/**
 * Simple way.
 */
function	part_one(array $keys): int
{
	list($card_key, $door_key) = $keys;
	$value = 1;
	$loopsize = 0;
	$cryptokey = 1;

	// Transform the subject number
	while ($value !== $card_key && $value !== $door_key && ++$loopsize)
		$value = ($value * 7) % 20201227;

	$snum = ($value === $card_key) ? $door_key : $card_key;
	while ($loopsize--)
		$cryptokey = ($cryptokey * $snum) % 20201227;
	return $cryptokey;
}

/**
 * Optimised Way.
 */
function	part_two(array $list)
{
}
