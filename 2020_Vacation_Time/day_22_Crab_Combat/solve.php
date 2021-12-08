<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: The winning score is \e[32m",
	part_one($data), "\e[0m\n";

// PART 2
echo "Part 2: The winning score after playing a recursive game is \e[32m",
	part_two($data), "\e[0m\n";


/**
 * Normal way.
 */
function	part_one(array $cards): int
{
	list($player_one, $player_two) = $cards;

	while (!empty($player_one) && !empty($player_two))
	{
		$card_one = array_shift($player_one);
		$card_two = array_shift($player_two);

		if ($card_one > $card_two)
			array_push($player_one, $card_one, $card_two);
		else
			array_push($player_two, $card_two, $card_one);
	}

	$winner = (empty($player_one)) ? $player_two : $player_one;
	$score = 0;

	foreach (array_reverse($winner) as $i => $card)
		$score += (($i + 1) * $card);
	return $score;
}

/**
 * Recursive Way.
 */
function	part_two(array $cards): int
{
	list($player_one, $player_two) = $cards;
	$score = 0;

	foreach (array_reverse(current(recursive_combat($player_one, $player_two))) as $i => $card)
		$score += (($i + 1) * $card);
	return $score;
}

function	recursive_combat(array $player_one, array $player_two)
{
	$snapshot = [];

	while (!empty($player_one) && !empty($player_two))
	{
		// Check and Prevent Infinite Loops
		foreach ($snapshot as $ss)
			if ($player_one === $ss[0] && $player_two === $ss[1])
				return ['one' => $player_one];
		// Save last Round.
		$snapshot[] = [$player_one, $player_two];

		$card_one = array_shift($player_one);
		$card_two = array_shift($player_two);
		$winner_one = $card_one > $card_two;

		if (count($player_one) >= $card_one && $card_two <= count($player_two))
		{
			$result = recursive_combat(
				array_slice($player_one, 0, $card_one),
				array_slice($player_two, 0, $card_two));
			
			$winner_one = (key($result) === 'one') ? true : false;
		}
		if ($winner_one)
			array_push($player_one, $card_one, $card_two);
		else
			array_push($player_two, $card_two, $card_one);
	}
	return (empty($player_one)) ? ['two' => $player_two] : ['one' => $player_one];
}

function	prep(string $data): array
{
	$cards = [];

	foreach (array_filter(explode("\n\n", $data)) as $split)
		$cards[] = array_filter(explode("\n", $split));
	array_shift($cards[0]);
	array_shift($cards[1]);
	return [
		array_map('intval', $cards[0]),
		array_map('intval', $cards[1]),
	];
}
