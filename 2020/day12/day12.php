<?PHP

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$puzzle = read($file);

echo "Part 1: \e[32m", part_one($puzzle), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle), "\e[0m\n";

function	part_one(array $puzzle): int
{
	$f_direction = 90;
	$coor = ['x' => 0, 'y' => 0];

	foreach ($puzzle as $cmd)
		move($cmd[0], $cmd[1], $coor, $f_direction);
	return abs($coor['x']) + abs($coor['y']);
}

function	move(string $cmd, int $amount, array &$coor, int &$f_direction)
{
	switch ($cmd)
	{
		case "N":	$coor['y'] += $amount; break;
		case "E":	$coor['x'] -= $amount; break;
		case "S":	$coor['y'] -= $amount; break;
		case "W":	$coor['x'] += $amount; break;
		case "F":	move_forward($f_direction, $amount, $coor); break;
		case "L":
			$f_direction -= $amount;
			while ($f_direction < 0)
				$f_direction += 360;
			break;
		case "R":
			$f_direction += $amount;
			while ($f_direction > 360)
				$f_direction -= 360;
			break;
		default:
			die("Error: Unknown instruction '".$amount."'.\n");
	}
}

function	move_forward(int $fd, int $amount, array &$coords)
{
	switch ($fd) {
		case 360:
		case 0:		$coords['y'] -= $amount; break;
		case 180:	$coords['y'] += $amount; break;
		case 90:	$coords['x'] += $amount; break;
		case 270:	$coords['x'] -= $amount; break;
		default:	die("Error: Bad degree for turning the ship: '$fd'.\n");
	}
}

function	part_two(array $puzzle): int
{
	$waypoint = ["x" => -10, "y" => 1];
	$fd = 90;
	$ship = ["x" => 0, "y" => 0];

	foreach ($puzzle as $cmd)
	{
		switch ($cmd[0]) {
			case "F":
				$ship["x"] += $waypoint["x"] * $cmd[1];
				$ship["y"] += $waypoint["y"] * $cmd[1];
				break;
			case "R":
			case "L":
				rotate_waypoint($cmd[0], $cmd[1], $waypoint, $fd);
				break;
			default:
				move($cmd[0], $cmd[1], $waypoint, $fd);
		}
	}
	return abs($ship['x']) + abs($ship['y']);
}

function	rotate_waypoint(string $d, int $am, array &$wp, int $fd)
{
	$c = $am / 90;

	while ($c--)
	{
		$tmp = $wp['x'];
		$wp['x'] = ($d === "R") ? $wp['y'] * -1 : $wp['y'];
		$wp['y'] = ($d === "R") ? $tmp : $tmp * -1;
	}
}

function	read(string $file): array
{
	$puzz = [];
	
	foreach (array_filter(explode("\n", file_get_contents($file))) as $ins)
		$puzz[] = [$ins[0], intval(substr($ins, 1))];
	return $puzz;
}
