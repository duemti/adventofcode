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
	if (--$x < 0) {
		$x = 1;
		foreach ($map as &$row) {
			array_unshift($row, $void);
			array_unshift($row, $void);
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
	if (--$y < 0) {
		$y = 1;
		array_unshift($map, array_fill(0, count($map[$y]), $void));
		array_unshift($map, array_fill(0, count($map[$y]), $void));
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
	if (++$y >= count($map)) {
		$map[] = array_fill(0, count($map[0]), $void);
		$map[] = array_fill(0, count($map[0]), $void);
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
	if (++$x >= count($map[$y])) {
		for ($i = 0; $i < count($map); $i++) {
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

function	compile_map(&$regex, $step, $x, $y, &$map)
{
	$position = [$x, $y];
	$branches = [];

	display($map);
	$void = "~";
	echo "x=".$x.", y=".$y."\n\n";

	while (isset($regex[$step])) {
		switch ($regex[$step]) {
			case "S":
				move_south($x, $y, $map, $void);
				break;
			case "E":
				move_east($x, $y, $map, $void);
				break;
			case "N":
				move_north($x, $y, $map, $void);
				break;
			case "W":
				move_west($x, $y, $map, $void);
				break;

			case "(":
				return ['status' => 'branching', 'at' => ['step' => $step, 'x' => $x, 'y' => $y]];
				break;
			case ")":
				$step++;
				foreach ($branches as &$b)
					$b['step'] = $step;
				return ['status' => 'branched', 'branches' => $branches];
				break;
			case "|":
				$branches[] = ['y' => $y, 'x' => $x];
				list($x, $y) = $position;
				break;
		}
		$step++;
	}
}

function	extract_paths($regex, &$i)
{
	$paths[] = [];

	for ( ; $i < count($regex); $i++) {
		$step = $regex[$i];

		if ($step == "(") {
			$i++;
			$opt = extract_paths($regex, $i);
			foreach ($paths as $p) {
				foreach ($opt as $o) {
					$new_paths[] = $p + $o;
				}
			}
			$paths = $new_paths;
			print_R($paths);
		}
		else if ($step == ")") {
			$options[] = $paths[0];
			return $options;
		}
		else if ($step == "|") {
			$options[] = $paths[0];
			$path[] = [];
		}
		else {
			foreach ($paths as &$p)
				$p[] = $step;
		}
	}
	return $paths;
}

function	solve($regex)
{
	$map = [
		['#', '?', '#'],
		['?', 'X', '?'],
		['#', '?', '#'],
	];
	$regex = str_split($regex);

	$i = 0;
	$paths = extract_paths($regex, $i);

	foreach ($paths as $p) {
		echo implode($p);
	}
	die();
	//compile_map($r, 0, 1, 1, $map);

	foreach ($map as &$row) {
		$row = array_map(function ($r) {
			return ($r == '?') ? '#' : $r;
		}, $row);
	}

	return $map;
}

if ($argc != 2)
	die("Please supply a file for input.");

$input = file_get_contents($argv[1]);
if ($input === false)
	die("Can't open the file.");

$input = digest_input($input);

$result = solve($input['regex']);

display($result);
foreach ($result as $row)
	$map[] = implode($row);
if (isset($input['expected_map'])) {
	if (json_encode($input['expected_map']) === json_encode($map))
		echo "\e[34mMap matched.\e[0m\n";
	else
		echo "\e[31mMap did not matched.\e[0m\n";
}
