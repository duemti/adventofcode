<?PHP

/**
 * @param $points a 4 dimenssional array representation of points
 */
function	firstPart(array $points)
{
	$constellations = [];

	foreach ($points as $point)
	{
		if (empty($inConst = checkPoint($constellations, $point)))
		{
			$constellations[] = [$point];
		} else {
			$newConst = [$point];
			foreach ($inConst as $id)
			{
				$newConst = array_merge($newConst, $constellations[$id]);
				unset($constellations[$id]);
			}
			$constellations[] = $newConst;
		}

	}
	return count($constellations);
}

function	checkPoint(array $constellations, array $point)
{
	$inConst = [];

	if (empty($constellations))
		return [];
	foreach ($constellations as $id => $const)
	{
		foreach ($const as $p)
		{
			// Calcullate manhattan distance
			$distance = abs($p['x'] - $point['x']) + abs($p['y'] - $point['y']) + abs($p['z'] - $point['z']) + abs($p['w'] - $point['w']);
			if ($distance <= 3)
				$inConst[] = $id;
		}
	}
	return array_unique($inConst, SORT_NUMERIC);
}

function	digestInput(string $input)
{
	$coordinates = [];
	$regex = "/(-?\d+),(-?\d+),(-?\d+),(-?\d+)/";
	$match = [];

	foreach (explode("\n", $input) as $line)
	{
		if (empty($line))
			continue ;
		if (!preg_match($regex, $line, $match))
		{
			if (!preg_match("/Result: (\d+)/", $line, $match))
				die("Error input: ".$line.PHP_EOL);
			$coordinates['result'] = intval($match[1]);
		} else {
			$coordinates['points'][] = [
				'x'	=> intval($match[1]),
				'y'	=> intval($match[2]),
				'z'	=> intval($match[3]),
				'w'	=> intval($match[4]),
			];
		}
	}
	return $coordinates;
}

$color = "\e[32m";
$input = file_get_contents($argv[1]);
$input = digestInput($input);
$result = firstPart($input['points']);
if (isset($input['result']) && $result !== $input['result'])
	$color = "\e[31m";

echo "There are ", $color, $result, "\e[0m constellations.\n";
