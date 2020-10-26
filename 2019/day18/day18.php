<?PHP
ini_set('extension', "json");
ini_set('extension', "ds.so");
ini_set('memory_limit', "2G");

if ($argc !== 2)
	exit("Usage:\n\t$argv[0] [file]\n");

$input = file_get_contents($argv[1]);
$map = [];
$position  = [];
foreach (array_filter(explode("\n", $input)) as $key => $line)
	$map[] = str_split($line);

$stime = microtime(true);
$allDistances = precomputeDistances($map);
//print_R($allDistances);
$steps = solve($allDistances, "@", 0, 0);
echo "Collecting every key took \e[32m", $steps, "\e[0m steps.\n";
echo (microtime(true) - $stime), PHP_EOL;
die;

function	solve(array $graph, string $vertex, int $collected_keys, int $steps): array
{
	$result = -1;

	foreach (array_keys($graph[$vertex]) as $key)
	{
		$info = $graph[$vertex][$key];

		if (($info['doors'] & $collected_keys) !== $info['doors']
			|| $collected_keys & keyToBinary($key))
			continue ;

		$res = solve($graph, $key, ($collected_keys | keyToBinary($key)), $steps + $info['steps']);
		// TODO: the steps are not calculated right.
		if ($res > 0 || $res < $result)
			$result = $res;
	}

	if (0 === ($collected_keys ^ bindec(str_repeat("1", count($graph) - 1))))
		return $steps;
	return $result;
}

function	keyToBinary(string $key) {
	return bindec(10 ** (ord($key) - 97));
}

//==============
$map[ $position['y'] ][ $position['x'] ] = ".";


// Priority Queue.
$paths = new \Ds\PriorityQueue();
$paths->push(json_encode([
	'steps'		=> 0,
	'position'	=> $position,
	'keys'		=> []
]), 0);
$steps = run($map, $paths);


function	run(array $map, \Ds\PriorityQueue $paths): int
{
	$steps = -1;

	while (! $paths->isEmpty())
	{
		$path = $paths->pop();

		$path = json_decode($path, true);

		$next_keys = getKeys($map, $path['position'], $path['steps'], $path['keys']);

		if (empty($next_keys))
		{
			$steps = $path['steps'];
			break ;
		}
		else
		{
			foreach ($next_keys as $fp)
				$paths->push(json_encode($fp), -$fp['steps']);
		}
	}
	return $steps;
}


function	getKeys(array $map, array $position, int $steps, array $keys)
{
	$paths = [];

	$keys_on_map = scan_area($map, [$position], $keys);
	if (empty($keys_on_map))
		return [];

	foreach ($keys_on_map as $id => $key)
	{
		$k = array_merge($keys, [$id]);
		sort($k);
		$paths[] = [
			'position'	=> $key['pos'],
			'steps'		=> $steps + $key['steps'],
			'keys'		=> $k
		];
	}
	return $paths;
}


function	scan_area(array $map, array $start, array $taken_keys)
{
	$steps = 0;
	$doors = [];
	$keys = [];
	$paths = [$start];

	while ( !empty($paths) )
	{
		$new_paths = [];
		foreach ($paths as $path)
		{
			$fr = $path[0];
			$x = $fr['x'];
			$y = $fr['y'];

			if ( isDoor($map[$y][$x]) && !in_array(ord($map[$y][$x]) + 32, $taken_keys))
				continue ;
			elseif ( $map[$y][$x] === '#' )
				continue ;
			elseif ( isKey($map[$y][$x]) && !in_array(ord($map[$y][$x]), $taken_keys))
				$keys[ ord($map[$y][$x]) ] = ['pos' => $fr, 'steps' => $steps];

			// UP
			if ( isset($map[$y - 1]) && isset($map[$y - 1][$x]) )
				$new_paths[] = array_merge([['x' => $x, 'y' => $y - 1]], $path);
			// DOWN
			if ( isset($map[$y + 1]) && isset($map[$y + 1][$x]) )
				$new_paths[] = array_merge([['x' => $x, 'y' => $y + 1]], $path);
			// LEFT
			if ( isset($map[$y]) && isset($map[$y][$x - 1]) )
				$new_paths[] = array_merge([['x' => $x - 1, 'y' => $y]], $path);
			// RIGHT
			if ( isset($map[$y]) && isset($map[$y][$x + 1]) )
				$new_paths[] = array_merge([['x' => $x + 1, 'y' => $y]], $path);

			$map[$y][$x] = "#";
		}
		$paths = $new_paths;
		$steps++;
	}
	return $keys;
}

function	precomputeDistances($map): array
{
	$distances = [];

	foreach ($map as $column_number => $row)
		foreach ($row as $row_number => $pin)
			if (isKey($pin) || $pin === "@")
				$distances[$pin] = computeDistances($map, $column_number, $row_number);
	return $distances;
}

function	computeDistances(array $map, int $y, int $x): array
{
	$paths = new \Ds\Queue();
	$new_paths = new \Ds\Queue();
	$distances = [];
	$steps = 0;
	
	$map[$y][$x] = ".";
	$paths->push( ['x' => $x, 'y' => $y, 'keys' => 0, 'doors' => 0] );
	while (! $paths->isEmpty())
	{
		$new_paths->clear();
		while (! $paths->isEmpty())
		{
			$path = $paths->pop();
			$x = $path['x'];
			$y = $path['y'];
			$vertex = $map[$y][$x];
			$keys = $path['keys'];
			$doors = $path['doors'];

			if ($vertex === "#")
				continue ;
			elseif (isKey($vertex))
			{
				$keys |= keyToBinary($vertex);
				$distances[$vertex] = ['steps' => $steps, 'doors' => $doors, 'keys' => $keys];
			}
			elseif (isDoor($vertex))
				$doors |= keyToBinary(strtolower($vertex));
			
			// UP
			if ( isset($map[$y - 1]) && isset($map[$y - 1][$x]) )
				$new_paths->push( ['x' => $x, 'y' => $y - 1, 'keys' => $keys, 'doors' => $doors] );
			// DOWN
			if ( isset($map[$y + 1]) && isset($map[$y + 1][$x]) )
				$new_paths->push( ['x' => $x, 'y' => $y + 1, 'keys' => $keys, 'doors' => $doors] );
			// LEFT
			if ( isset($map[$y]) && isset($map[$y][$x - 1]) )
				$new_paths->push( ['x' => $x - 1, 'y' => $y, 'keys' => $keys, 'doors' => $doors] );
			// RIGHT
			if ( isset($map[$y]) && isset($map[$y][$x + 1]) )
				$new_paths->push( ['x' => $x + 1, 'y' => $y, 'keys' => $keys, 'doors' => $doors] );
			$map[$y][$x] = "#";
		}
		$paths = $new_paths->copy();
		$steps++;
	}
	return $distances;
}

function	isKey(string $ch): bool
{
	$ch = ord($ch);
	return (97 <= $ch && $ch <= 122) ? true : false;
}

function	isDoor(string $ch): bool
{
	$ch = ord($ch);
	return (65 <= $ch && $ch <= 90) ? true : false;
}

