<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_filter(array_map('trim', $data));

// PART 1
echo "Part 1: The total syntax error score of corrupt lines is \e[32m",
	part_one($data), "\e[0m\n";

// PART 2
echo "Part 2: The Middle syntax error score of incompleted lines is \e[32m",
	part_two($data), "\e[0m\n";

function	part_one(array &$subsystem): int
{
	$score = 0;
	$tags = [
		']' => '[',
		')' => '(',
		'}' => '{',
		'>' => '<'
	];

	foreach ($subsystem as $line_nb => $line)
	{
		$open_tags = [];

		foreach (str_split($line) as $char)
		{
			if (in_array($char, $tags))
				$open_tags[] = $char;
			elseif (array_pop($open_tags) !== $tags[ $char ])
			{
				$score += [
					')' => 3,
					']' => 57,
					'}' => 1197,
					'>' => 25137
				][ $char ];

				unset($subsystem[ $line_nb ]);
				break ;
			}
		}
	}
	return $score;
}

/**
 * Complete the incomplete lines
 */
function	part_two(array $subsystem): int
{
	$score = [];
	$tags = ['(', '[', '{', '<'];

	foreach ($subsystem as $line_nb => $line)
	{
		$score[ $line_nb ] = 0;
		$open_tags = [];

		foreach (str_split($line) as $char)
		{
			if (in_array($char, $tags))
				$open_tags[] = $char;
			else
				array_pop($open_tags);
		}
		while ($tag = array_pop($open_tags))
			$score[ $line_nb ] = $score[ $line_nb ] * 5 + array_search($tag, $tags) + 1;
	}
	sort($score);
	return $score[ (int)(count($score) / 2) ];
}
