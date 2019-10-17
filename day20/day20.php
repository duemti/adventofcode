<?PHP

function	digest_input(string $input): array
{
	$input = explode("\n", $input);
	if ($input[0] === "Test_Regex") {
		$dinput['regex'] = $input[1];
		$dinput['expected_result'] = $input[2];
		for ($i = 3; $i < count($input); $i++)
			if (!empty($input[$i]))
				$dinput['expected_map'][] = $input[$i];
	}
	else
		$dinput['regex'] = $input[0];

	return $dinput;
}

function	display($map)
{
	foreach ($map as $row) {
		echo implode($row) . PHP_EOL;
	}
}

function	map_point(&$point, $to, $matches)
{
	if ($point == $matches)
		$point = $to;
}

function	move_west(&$x, &$y, &$map, $void)
{
	$map[$y][--$x] = '|';
	--$x;

	if (! isset($map[$y][$x])) {

		foreach ($map as &$row) {
			$row[$x]		= $void;
			$row[$x - 1]	= $void;
			ksort($row);
		}
	}
	map_point($map[$y - 1][$x], '?', $void);
	map_point($map[$y][$x], '.', $void);
	map_point($map[$y + 1][$x], '?', $void);

	$map[$y - 1][$x - 1] = '#';
	map_point($map[$y][$x - 1], '?', $void);
	$map[$y + 1][$x - 1] = '#';
}

function	move_north(&$x, &$y, &$map, $void)
{
	$map[--$y][$x] = '-';
	--$y;

	if (! isset($map[$y])) {
		$keys = range(array_key_first($map[$y + 1]), array_key_last($map[$y + 1]));
		$map[$y]		= array_fill_keys($keys, $void);
		$map[$y - 1]	= array_fill_keys($keys, $void);
		ksort($map);
	}
	map_point($map[$y][$x - 1], '?', $void);
	map_point($map[$y][$x], '.', $void);
	map_point($map[$y][$x + 1], '?', $void);

	$map[$y - 1][$x - 1] = '#';
	map_point($map[$y - 1][$x], '?', $void);
	$map[$y - 1][$x + 1] = '#';
}

function	move_south(&$x, &$y, &$map, $void)
{
	$map[++$y][$x] = '-';
	++$y;

	if (! isset($map[$y])) {
		$keys = range(array_key_first($map[$y - 1]), array_key_last($map[$y - 1]));
		$map[] = array_fill_keys($keys, $void);
		$map[] = array_fill_keys($keys, $void);
	}
	map_point($map[$y][$x - 1], "?", $void);
	map_point($map[$y][$x], '.', $void);
	map_point($map[$y][$x + 1], '?', $void);

	$map[$y + 1][$x - 1] = '#';
	map_point($map[$y + 1][$x], '?', $void);
	$map[$y + 1][$x + 1] = '#';
}

function	move_east(&$x, &$y, &$map, $void)
{
	$map[$y][++$x] = '|';
	++$x;

	if (! isset($map[$y][$x])) {
		for ($i = array_key_first($map); $i <= array_key_last($map); $i++) {
			$map[$i][] = $void;
			$map[$i][] = $void;
		}
	}
	map_point($map[$y - 1][$x], '?', $void);
	map_point($map[$y][$x], '.', $void);
	map_point($map[$y + 1][$x], '?', $void);

	$map[$y - 1][$x + 1] = '#';
	map_point($map[$y][$x + 1], '?', $void);
	$map[$y + 1][$x + 1] = '#';
}

function	mapping($step, $pos, &$map)
{
	$void = "~";

	switch ($step) {
		case "S":
			move_south($pos[0], $pos[1], $map, $void);
			break;
		case "E":
			move_east($pos[0], $pos[1], $map, $void);
			break;
		case "N":
			move_north($pos[0], $pos[1], $map, $void);
			break;
		case "W":
			move_west($pos[0], $pos[1], $map, $void);
			break;
	}
	return $pos;
}

function	extract_paths($regex, &$map, $init_pos, &$i)
{
	$positions = $init_pos;
	$options_pos = [];

	for ( ; $i < count($regex); $i++) {
		$step = $regex[$i];

		switch ($step) {
		case "(":
			$i++;
			$positions = array_unique(extract_paths($regex, $map, $positions, $i), SORT_REGULAR);
			break;
		case ")":
			return array_merge($options_pos, $positions);
		case "|":
			$options_pos = array_merge($positions, $options_pos);
			$positions = $init_pos;
			break;
		case "N":
		case "S":
		case "E":
		case "W":
			$pos = [];
			foreach ($positions as $p) {
				$pos[] = mapping($step, $p, $map);
			}
			$positions = $pos;
			break;
		}
	}
	return $init_pos;
}

function	furthest_room($map)
{
	$routes = [["y" => 1, "x" => 1, "d" => 0]];
	$doors = 0;
	$rooms = 0;

	while (1) {

		$new_routes = [];
		foreach ($routes as $r) {
			// check north for door
			if ($map[$r['y'] - 1][$r['x']] == "-") {
				$new_routes[] = ['y' => $r['y'] - 2, 'x' => $r['x'], 'd' => $r['d'] + 1];
				$map[$r['y'] - 1][$r['x']] = $doors;
				if ($doors >= 999)
					$rooms++;
			}
			// check south for door
			if ($map[$r['y'] + 1][$r['x']] == "-") {
				$new_routes[] = ['y' => $r['y'] + 2, 'x' => $r['x'], 'd' => $r['d'] + 1];
				$map[$r['y'] + 1][$r['x']] = $doors;
				if ($doors >= 999)
					$rooms++;
			}
			// check west for door
			if ($map[$r['y']][$r['x'] - 1] == "|") {
				$new_routes[] = ['y' => $r['y'], 'x' => $r['x'] - 2, 'd' => $r['d'] + 1];
				$map[$r['y']][$r['x'] - 1] = $doors;
				if ($doors >= 999)
					$rooms++;
			}
			// check east for door
			if ($map[$r['y']][$r['x'] + 1] == "|") {
				$new_routes[] = ['y' => $r['y'], 'x' => $r['x'] + 2, 'd' => $r['d'] + 1];
				$map[$r['y']][$r['x'] + 1] = $doors;
				if ($doors >= 999)
					$rooms++;
			}
		}
		if (empty($new_routes))
			break;
		$routes = $new_routes;
		$doors++;
	}
	return ['doors' => $doors, 'rooms' => $rooms];
}

function	solve($regex)
{
	$map = [
		['#', '?', '#'],
		['?', 'X', '?'],
		['#', '?', '#'],
	];
	$positions = [
		[1, 1]
	];
	$regex = str_split($regex);

	$i = 0;
	extract_paths($regex, $map, $positions, $i);


	foreach ($map as &$row) {
		$row = array_map(function ($r) {
			return ($r == '?') ? '#' : $r;
		}, $row);
	}

	$res = furthest_room($map);

	return [
		"map" => $map,
		"res" => (int)$res['doors'],
		"rooms" => (int)$res['rooms']
	];
}

if ($argc != 2)
	die("Please supply a file for input.");

$input = file_get_contents($argv[1]);
if ($input === false)
	die("Can't open the file.");

$input = digest_input($input);

$result = solve($input['regex']);

display($result["map"]);
echo "Furthest room is ", $result['res'], " doors away!\n";
echo "There are ",$result['rooms']," rooms that have a shortest path from my current location that pass through at least 1000 doors.\n";
foreach ($result["map"] as $row)
	$map[] = implode($row);
if (isset($input['expected_map'])) {
	if (json_encode($input['expected_map']) === json_encode($map))
		echo "\e[34mMap matched.\e[0m\n";
	else
		echo "\e[31mMap did not matched.\e[0m\n";
}
if (isset($input['expected_result'])) {
	if ($input['expected_result'] == $result["res"])
		echo $input['expected_result'] , "\e[34m Result matched. ", $result["res"] ,"\e[0m\n";
	else
		echo $input['expected_result'] , "\e[31m Result did not matched. ", $result["res"] ,"\e[0m\n";
}
