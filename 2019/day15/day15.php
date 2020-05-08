<?PHP
include __DIR__.'/IntcodeProcessor.php';

if ($argc !== 2)
	exit("Usage:\n\t$argv[0] [file]\n");

$software = new IntcodeProcessor(array_filter(explode(",", file_get_contents($argv[1]))), true);

$area = [[0]];
$frontier = [['x' => 0, 'y' => 0] + $software->dump_state()];
$steps = 0;
$empty_space = '.';
while ( !empty($frontier) )
{
	$steps++;
	$new_frontier = [];
	foreach ($frontier as $current)
	{
		$nf = find_oxygen_system($software, $area, $current, $empty_space);
		if (isset($nf['found']))
		{
			echo "Found the oxygen system \e[32m$steps\e[0m moves away.\n";
			$new_frontier = [$nf['found']];
			// because it will run the last time but there would not be empty places for the oxygen to fill.
			$steps = -1;
			$empty_space = 'O';
			break 1;
		}
		$new_frontier = array_merge($new_frontier, $nf);
	}
	$frontier = $new_frontier;
}
echo "The oxygen filled the entire area in \e[32m$steps\e[0m minutes.\n";

function	find_oxygen_system($software, array &$area, array $current, string $empty_space): array
{
	$new_frontier = [];

	foreach ([1, 2, 3, 4] as $direction)
	{
		// We need the exact memory state to continue forward.
		$software->restart( $current['memory'], $current['ip'] );

		// input movement command.
		$software->input = [$direction];

		$software->run();
		if ($software->end)
			exit("Error: Software has halted.\n");

		// output status based on previous command.
		$output = intval(array_pop($software->output));

		$coor = move($direction, $current['x'], $current['y']);
		if ( isset($area[ $coor['y'] ][ $coor['x'] ])
			&& ($area[ $coor['y'] ][ $coor['x'] ] === '#'
			|| $area[ $coor['y'] ][ $coor['x'] ] === $empty_space))
			continue;

		$area[ $coor['y'] ][ $coor['x'] ] = set($output, $empty_space);
		// If the oxygen system is found.
		if ($output === 2)
			return ['found' => $coor + $software->dump_state()];
		elseif ($output === 0)
			continue;

		$new_frontier[] = array_merge($coor, $software->dump_state());
	}
	return $new_frontier;
}

function	set($o, string $empty_space): string
{
	switch ($o)
	{
		case 0:
			return '#';
		case 1:
			return $empty_space;
		case 2:
			return 'O';
	}
}

function	move(int $direction, int $x, int $y): array
{
	switch ($direction)
	{
		case 1:
			$y--;
			break;
		case 2:
			$y++;
			break;
		case 3:
			$x--;
			break;
		case 4:
			$x++;
			break;
		default:
			exit("Error: Unknown direction '$direction'\n");
	}
	return ['x' => $x, 'y' => $y];
}
