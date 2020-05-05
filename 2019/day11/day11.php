<?PHP
include_once __DIR__.'/IntcodeProcessor.php';

if ($argc !== 3)
	exit("Usage:\n\t{$argv[0]} [input file] [starting panel color]\n0 for white color\n1 for black color\n");
$program = new IntcodeProcessor(explode(",", file_get_contents($argv[1])), true);


$black = 0;
$white = 1;
$compass = [
	'up'	=> ['left', 'right'],
	'right'	=> ['up', 'down'],
	'down'	=> ['right', 'left'],
	'left'	=> ['down', 'up']
];
$direction = 'up';
$spacecraft[0][0] = intval($argv[2]);

$minx = 0;
$maxx = 0;
$x = 0;
$miny = 0;
$maxy = 0;
$y = 0;
while (1)
{
	if ( !isset($spacecraft[$y][$x]) )
		$spacecraft[$y][$x] = $black;
	$program->input = [$spacecraft[$y][$x]];

	$program->run();
	$paint = array_pop($program->output);
	$program->run();
	$move_direction = array_pop($program->output);
	if ($program->end)
		break;

	$direction = $compass[ $direction ][ $move_direction ];
	$spacecraft[$y][$x] = $paint;
	move($direction, $x, $y);

	if ($minx > $x)
		$minx = $x;
	elseif ($x > $maxx)
		$maxx = $x;
	if ($miny > $y)
		$miny = $y;
	elseif ($y > $maxy)
		$maxy = $y;
}

$count = 0;
// Print.
for ($y = $miny; $y <= $maxy; $y++)
{
	for ($x = $minx; $x <= $maxx; $x++)
	{
		if ( isset($spacecraft[$y][$x]) )
		{
			if ($spacecraft[$y][$x])
				echo "\e[37m#";
			else
				echo "\e[30;1m.";
			$count++;
		}
		else
			echo "\e[30;1m.";
	}
	echo "\e[0m", PHP_EOL;
}

echo "Painted \e[32m", $count, "\e[0m panels.\n";

function	move(string $d, int &$x, int &$y)
{
	switch ($d)
	{
		case 'up':
			$y--;
			break;
		case 'right':
			$x++;
			break;
		case 'down':
			$y++;
			break;
		case 'left':
			$x--;
			break;
	}
}
