<?PHP
include __DIR__.'/IntcodeProcessor.php';

if ($argc !== 3)
	exit("Usage:\n\t$argv[0] [file] [y/n for continuous video feed]\n");

$soft = explode(",", trim(file_get_contents($argv[1])));

$prog = new IntcodeProcessor($soft, ['input' => true, 'output' => true]);

$scaff = [[]];
$robot = [];
$y = 0;
$x = 0;
while ( !$prog->end)
{
	$prog->run();
	$output = chr( array_pop($prog->output) );
	
	if ($output == "\n")
	{
		$y++;
		$x = -1;
	}
	elseif ($output == "#")
		$scaff[$y][$x] = $output;
	elseif ($output == "^")
		$robot = [$x, $y];
	$x++;
}

$sum = solve($scaff);
$path = get_path($scaff, "^", $robot[0], $robot[1]);
$func = find_func($path, 3);
$input = prepare_input($func, $path);
$dust = run($input, $prog, $y, $argv[2]);
echo str_repeat("\n", $y + 11);
echo "The sum of alignment parameters is \e[32m$sum\e[0m\n";
echo "The robot collected \e[32m$dust\e[0m dust particles.\n";

function	run(array $input, $prog, int $height, string $feed = "n"): int
{
	$prog->restart();
	$prog->edit_memory(0, 2);
	$input['feed'] = [ord($feed), 10];
	$tmp = 11;
	$h = 0;

	while (1)
	{
		$prog->run();
		if ($prog->end)
			break ;
		if ($h == $height + $tmp)
		{
			echo str_repeat("\r\033[F", $height + $tmp);
			$h = 0;
			$tmp = 0;
		}
		if ($prog->halt_cause === "input")
		{
			$in = array_shift($input);
			foreach ($in as $i)
				echo chr($i);
			$h++;
			$prog->input = $in;
		}
		$ch = array_pop($prog->output);
		if ($ch > 120)
			$dust = $ch;
		else
		{
			$ch = chr($ch);
			if (in_array($ch, ['^', 'v', '<', '>']))
				echo "\e[32m$ch\e[0m";
			else
				echo $ch;
			if ($ch == "\n")
				$h++;
		}
	}
	return $dust;
}

function	prepare_input(array $func, array $path)
{
	$in = [];
	$path = implode($path, ",").",";
	$a = implode($func[0], ",").",";
	$b = implode($func[1], ",").",";
	$c = implode($func[2], ",").",";

	$path = str_replace($c, "C,", str_replace($b, "B,", str_replace($a, "A,", $path)));
	foreach (str_split($path) as $f)
		$in['main'][] = ord($f);
	array_splice($in['main'], -1, 1, [10]);
	foreach ($func as $k => $f)
	{
		$in[$k] = [];
		foreach ($f as $i)
			$in[$k] = array_merge($in[$k], array_map("ord", str_split($i)), [44]);
		array_splice($in[$k], -1, 1, [10]);
	}
	return $in;
}

function	find_func(array $path, int $parts)
{
	$cmd = implode($path, ".") . ".";

	for ($depth = 0; $depth < count($path); $depth++)
	{
		for ($i = 2; $i <= 20 && $i + $depth < count($path); $i += 2)
		{
			$f = implode(array_slice($path, $depth, $i / 2), ".") . ".";
			$cmd = str_replace($f, "", implode($path, ".") . ".");

			if (empty($cmd))
				return [array_filter(explode(".", $f))];
			elseif ($parts > 1)
			{
				$next = find_func(array_filter(explode(".", $cmd)), $parts - 1);
				if ($next !== false)
					return array_merge([array_filter(explode(".", $f))], $next);
			}
		}
	}
	return false;
}

function	get_path(array $scaff, string $robot, int $x, int $y)
{
	// Move left or right only.
	$moves = [];

	while (1)
	{
		$right = 0;
		$left = 0;

		switch ($robot)
		{
			case "^":
				if ( isset($scaff[$y][$x + 1]) )
				{
					$robot = ">";
					while ( isset($scaff[$y][++$x]) )
						$right++;
					$x--;
				}
				elseif ( isset($scaff[$y][$x - 1]) )
				{
					$robot = "<";
					while ( isset($scaff[$y][--$x]) )
						$left++;
					$x++;
				}
				break;
			case "v":
				if ( isset($scaff[$y][$x + 1]) )
				{
					$robot = ">";
					while ( isset($scaff[$y][++$x]) )
						$left++;
					$x--;
				}
				elseif ( isset($scaff[$y][$x - 1]) )
				{
					$robot = "<";
					while ( isset($scaff[$y][--$x]) )
						$right++;
					$x++;
				}
				break;
			case ">":
				if ( isset($scaff[$y - 1][$x]) )
				{
					$robot = "^";
					while ( isset($scaff[$y - 1][$x]) )
					{
						$y -= 1;
						$left++;
					}
				}
				elseif ( isset($scaff[$y + 1][$x]) )
				{
					$robot = "v";
					while ( isset($scaff[++$y][$x]) )
						$right++;
					$y--;
				}
				break;
			case "<":
				if ( isset($scaff[$y - 1][$x]) )
				{
					$robot = "^";
					while ( isset($scaff[--$y][$x]) )
						$right++;
					$y++;
				}
				elseif ( isset($scaff[$y + 1][$x]) )
				{
					$robot = "v";
					while ( isset($scaff[++$y][$x]) )
						$left++;
					$y--;
				}
				break;
		}
		if (0 === $left && 0 === $right)
			break;
		$moves[] = ($left) ? "L,".$left : "R,".$right;
	}
	return $moves;
}

function	solve(array $scaffold): int
{
	$sum = [];

	foreach (array_keys($scaffold) as $y)
	{
		foreach (array_keys($scaffold[$y]) as $x)
		{
			if (isset($scaffold[$y - 1]) && isset($scaffold[$y - 1][$x])
				&& isset($scaffold[$y + 1]) && isset($scaffold[$y + 1][$x])
				&& isset($scaffold[$y]) && isset($scaffold[$y][$x - 1])
				&& isset($scaffold[$y]) && isset($scaffold[$y][$x + 1]))
				$sum[] = $y * $x;
		}
	}
	return array_sum($sum);
}
