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

function	compile_map($regex)
{
	$map = [
		['#', '?', '#'],
		['?', 'X', '?'],
		['#', '?', '#'],
	];
	$pos[] = [
		'y' => 1,
		'x' => 1,
	];
	$y = 1;
	$x = 1;

	foreach (str_split($regex) as $step) {
		if ($step == "^" || $step == "$")
			continue;
		display($map);
		echo "x=".$x.", y=".$y."\n\n";

		switch ($step) {
			case "S":
				$map[++$y][$x] = '-';
				if (++$y >= count($map)) {
					$map[] = array_fill(0, count($map[0]), " ");
					$map[] = array_fill(0, count($map[0]), " ");
				}
				$map[$y]		[$x - 1]	= '?';
				$map[$y]		[$x]		= '.';
				$map[$y]		[$x + 1]	= '?';

				$map[$y + 1]	[$x - 1]	= '#';
				$map[$y + 1]	[$x]		= '?';
				$map[$y + 1]	[$x + 1]	= '#';
				break;
			case "E":
				$map[$y][++$x] = '|';
				if (++$x >= count($map[$y])) {
					for ($i = 0; $i < count($map); $i++) {
						$map[$i][] = " ";
						$map[$i][] = " ";
					}
				}
				$map[$y - 1]	[$x] = '?';
				$map[$y]		[$x] = '.';
				$map[$y + 1]	[$x] = '?';

				$map[$y - 1]	[$x + 1] = '#';
				$map[$y]		[$x + 1] = '?';
				$map[$y + 1]	[$x + 1] = '#';
				break;
			case "N":
				$map[--$y][$x] = '-';
				if (--$y < 0) {
					$y = 1;
					array_unshift($map, array_fill(0, count($map[$y]), " "));
					array_unshift($map, array_fill(0, count($map[$y]), " "));
				}
				$map[$y]		[$x - 1]	= '?';
				$map[$y]		[$x]		= '.';
				$map[$y]		[$x + 1]	= '?';

				$map[$y - 1]	[$x - 1]	= '#';
				$map[$y - 1]	[$x]		= '?';
				$map[$y - 1]	[$x + 1]	= '#';
				break;
			case "W":
				$map[$y][--$x] = '|';
				if (--$x < 0) {
					$x = 1;
					foreach ($map as &$row) {
						array_unshift($row, " ");
						array_unshift($row, " ");
					}
				}
				$map[$y - 1]	[$x] = '?';
				$map[$y]		[$x] = '.';
				$map[$y + 1]	[$x] = '?';

				$map[$y - 1]	[$x - 1] = '#';
				$map[$y]		[$x - 1] = '?';
				$map[$y + 1]	[$x - 1] = '#';
				break;
			case "(":
				$pos[] = ['y' => $y, 'x' => $x];
				break;
			case ")"://TODO
			case "|":
				list($y, $x) = array_pop($pos);
				break;
		}
	}
	foreach ($map as &$row) {
		$row = array_map(function ($r) {
			return ($r == '?') ? '#' : $r;
		}, $row);
	}
	return $map;
}

function	solve($regex)
{
	$map = compile_map($regex);

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
