<?PHP

foreach (array_filter(explode("\n", file_get_contents($argv[1]))) as $row)
	$map[] = str_split($row);

$loc = check($map);
echo "From ", $loc['x'], ",", $loc['y'], " can be observed \e[32m", $loc['count'], "\e[0m asteroids.\n";
if (isset($loc['200th']))
	echo "The 200'th asteroid vaporized is at ", $loc['200th']['x'], ",", $loc['200th']['y'], " and x * 100 + y = \e[32m", ($loc['200th']['x'] * 100 + $loc['200th']['y']), "\e[0m\n";
else
	echo "Number of vaporized asteroids did not reach 200!\n";

function	check($map)
{
	foreach ($map as $y => $row)
		foreach ($row as $x => $pin)
			if ($pin === '#')
				$asteroids[] = ['y' => $y, 'x' => $x, 'sight' => []];

	for ($id = 0; $id < count($asteroids); $id++)
	{
		$sights = check_sight($asteroids, $id);
		if (!isset($most_sights) || $sights > $most_sights[1])
			$most_sights = [$id, $sights];
	}

	$station = $most_sights[0];
	$asteroids[$station]['count'] = count($asteroids[$station]['sight']);
	$asteroids_destroyed = 0;
	while ( !empty($laser_station = $asteroids[$station]['sight']) )
	{
		$laser_station = my_sort($laser_station);

		// Destroy all asteroids in sight.
		foreach ($laser_station as $id => $not_used)
		{
			if (++$asteroids_destroyed === 200)
				$asteroids[$station]['200th'] = $asteroids[$id];
			unset($asteroids[$id]);
		}
		// Reset each remaining asteroids sight.
		foreach ($asteroids as $id => $not_used)
			$asteroids[$id]['sight'] = [];
		check_sight($asteroids, $station);
	}
	return $asteroids[$station];
}

function	check_sight(array &$asteroids, int $current)
{
	$curr_y = $asteroids[$current]['y'];
	$curr_x = $asteroids[$current]['x'];

	foreach ($asteroids as $id => $not_used)
	{
		if ($id === $current)
			continue ;

		$angle = atan2($asteroids[$id]['y'] - $curr_y, $asteroids[$id]['x'] - $curr_x);

		// If the 2 asteroids can see each other.
		if (! in_array($angle, $asteroids[$current]['sight']))
			$asteroids[$current]['sight'][$id] = $angle;
		else
		{
			$cid = array_search($angle, $asteroids[$current]['sight']);
			$as1 = $asteroids[$cid];
			$as2 = $asteroids[$id];
			$dis1 = sqrt(($as1['x'] - $curr_x) ** 2 + ($as1['y'] - $curr_y) ** 2);
			$dis2 = sqrt(($as2['x'] - $curr_x) ** 2 + ($as2['y'] - $curr_y) ** 2);
			if ($dis2 < $dis1)
			{
				$asteroids[$current]['sight'][$id] = $angle;
				unset($asteroids[$current]['sight'][$cid]);
			}
		}
	}
	return count($asteroids[$current]['sight']);
}

// Sort all asteroids that i may destroy them clockwise from the station where laser is located.
function	my_sort(array $sight): array
{
	foreach (array_keys($sight) as $id)
		if ($sight[$id] < 0.0)
			$sight[$id] += M_PI * 2;
	asort($sight);

	foreach (array_reverse(array_keys($sight)) as $id)
	{
		$angle = $sight[$id];

		if ($angle >= (3 * M_PI / 2))
		{
			unset($sight[$id]);
			$sight = [$id => $angle] + $sight;
		}
	}
	return $sight;
}
