<?PHP
include_once __DIR__.'/IntcodeProcessor.php';

if ($argc !== 2)
	exit("Usage:\n\t$argv[0] [file]\n");
$game = new IntcodeProcessor(explode(",", file_get_contents($argv[1])), true);

// Insert coin
$game->edit_memory(0, 2);
$screen = [];
$first_time = true;
while ( !$game->end)
{
	$game->run();
	if ($game->halt_cause === "input")
	{
		if ($paddle['x'] === $ball['x'])
			$game->input[] = 0;
		elseif ($paddle['x'] > $ball['x'])
			$game->input[] = -1;
		else
			$game->input[] = 1;
	}
	elseif ($game->halt_cause === "output")
	{
		$x = intval(array_pop($game->output));
		$game->run();
		$y = intval(array_pop($game->output));
		$game->run();
		$tile_id = intval(array_pop($game->output));

		$screen[$y][$x] = $tile_id;
		if ($tile_id === 3)
			$paddle = ['x' => $x, 'y' => $y];
		elseif ($tile_id === 4)
			$ball = ['x' => $x, 'y' => $y];
		continue ;
	}

	if ($first_time)
	{
		$first_time = false;
		part_one($screen);
	}
	else
		for ($i = 0; $i - 2 < count($screen); $i++)
			echo "\r\033[A";
	echo PHP_EOL, "SCORE: \e[33m", $screen[0][-1], "\e[0m\n";
	display($screen);
}

function	part_one(array $screen)
{
	$blocks = 0;
	foreach ($screen as $row)
		foreach ($row as $tile)
			if ($tile == 2)
				$blocks++;
	echo "There are \e[32m$blocks\e[0m blocks on the screen.\n";
}

function	display(array $screen)
{
	foreach ($screen as $row)
	{
		for($x = 0; isset($row[$x]); $x++)
		{
			switch ($row[$x])
			{
				case 0:
					echo "\e[37m.";
					break;
				case 1:
					echo "\e[37;1m|";
					break;
				case 2:
					echo "\e[30;1m#";
					break;
				case 3:
					echo "\e[34;1m-";
					break;
				case 4:
					echo "\e[32mo";
					break;
			}
			echo "\e[0m";
		}
		echo PHP_EOL;
	}
}
