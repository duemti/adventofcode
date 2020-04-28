<?PHP

// Universal Orbit Map
$orbit_map = array_filter(explode("\n", file_get_contents($argv[1])));

// Verify map for corruption
foreach ($orbit_map as $orbit)
{
	$o = explode(")", $orbit);
	$om[$o[0]][] = $o[1];
}


echo "Minimum Orbital transfers till SAN: \e[32m", minimum_orbital_transfers($om), "\e[0m", PHP_EOL;
$om["orbits"] = 0;
checksum("COM", $om);
echo "Total orbits: \e[32m", $om["orbits"], "\e[0m", PHP_EOL;

// Part One.
function	checksum(string $space_object, array &$orbit_map): int
{
	$indirect_orbits = 0;
	// Current Object from the map.
	$center_of_mass = $orbit_map[$space_object];
	$direct_orbits = count($center_of_mass);

	// Go through all orbits of current Center Of Mass.
	for ($i = 0; $i < $direct_orbits; $i++)
	{
		$current_orbit = $center_of_mass[$i];
		
		if (isset($orbit_map[$current_orbit]))
			$indirect_orbits += checksum($current_orbit, $orbit_map);
	}

	$orbit_map["orbits"] += $indirect_orbits + $direct_orbits;
	return $indirect_orbits + $direct_orbits;
}

//Part Two.
function	minimum_orbital_transfers(array $orbit_map)
{
	if (empty($you[] = search('YOU', $orbit_map)))
		return "\e[31mError: 'YOU' not found.";
	if (empty($santa = search('SAN', $orbit_map)))
		return "\e31mError: 'SAN' not found.";

	$count = 0;
	while (1)
	{
		$new_you = [];

		foreach ($you as $me)
		{
			if (!isset($orbit_map[$me]))
				continue;

			if ($santa === $me)
				break 2;

			foreach ($orbit_map[$me] as $orbit)
			{
				if (!empty($orbit_map[$orbit]))
					$new_you[] = $orbit;
			}
			$orbit_map[$me] = [];

			$orbit = search($me, $orbit_map);
			if ($orbit && !isset($orbit['checked']))
				$new_you[] = $orbit;
		}
		$you = array_unique($new_you, SORT_STRING);
		$count++;
	}
	return $count;
}

function	search(string $needle, array $orbit_map)
{
	foreach ($orbit_map as $key => $orbits)
		if (in_array($needle, $orbits))
			return $key;
	return null;
}
