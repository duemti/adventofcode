<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file_get_contents($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = prep($data);

// PART 1
echo "Part 1: The digits 1, 4, 7 & 8 appear exactly \e[32m",
	part_one($data), "\e[0m times in output values.\n";

// PART 2
echo "Part 1: All the output values add up to \e[32m",
	part_two($data), "\e[0m\n";


function	part_one(array $journal): int
{
	$count = 0;

	foreach (array_column($journal, 1) as $output_values)
		foreach ($output_values as $ov)
			if (in_array(strlen($ov), [2, 4, 3, 7], true))
				$count++;
	return $count;
}

function	part_two(array $journal): int
{
	$output_values_sum = 0;

	foreach ($journal as $entry)
	{
		$segments = array_fill_keys(range("a", "g"), range("a", "g"));

		foreach ($entry[0] as $wire)
			easy_digits($segments, str_split($wire));

		$segments = [$segments];
		foreach ($entry[0] as $wire)
		{
			if (!in_array(strlen($wire), [5, 6]))
				continue ;
			$next_wires = [];

			foreach ($segments as $segm)
				hard_digits($segm, str_split($wire), $next_wires);
			$segments = $next_wires;
		}

		if (count($segments) > 1)
			die("ERROR: More than one combinations found for one display.\n");
		foreach ($segments[0] as $k => $s)
			$config[ $k ] = current($s);
		// Form the output value.
		$output_values_sum += form_output_value($entry[1], $config);
	}
	return $output_values_sum;
}

function	form_output_value(array $ov, array $config): int
{
	$result = "";

	foreach ($ov as $value)
	{
		$val = str_split($value);
		$val = implode(array_keys(array_intersect($config, $val)));
		switch ($val)
		{
			case "abcefg": $result .= "0"; break;
			case "cf": $result .= "1"; break;
			case "acdeg": $result .= "2"; break;
			case "acdfg": $result .= "3"; break;
			case "bcdf": $result .= "4"; break;
			case "abdfg": $result .= "5"; break;
			case "abdefg": $result .= "6"; break;
			case "acf": $result .= "7"; break;
			case "abcdefg": $result .= "8"; break;
			case "abcdfg": $result .= "9"; break;
			default: die("ERROR: Unknown digit '$val'\n");
		}
	}
	return intval($result);
}

function	easy_digits(array &$segments, array $wire)
{
	$marks = [];

	switch (count($wire))
	{
		case 2: $marks = ["c", "f"]; break; // DIGIT 1
		case 4: $marks = ["b", "c", "d", "f"]; break; // DIGIT 4
		case 3: $marks = ["a", "c", "f"]; break; // DIGIT 7
		case 7: $marks = ["a", "b", "c", "d", "e", "f", "g"]; break; // DIGIT 8
	}
	$segments = mark($segments, $marks, $wire);
}

function	hard_digits(array $segments, array $wire, array &$next_wires)
{
	// Possible digits: 2, 3, 5
	$five_seg = [
		["a", "c", "d", "e", "g"],
		["a", "c", "d", "f", "g"],
		["a", "b", "d", "f", "g"]
	];
	// Possible digits: 0, 6, 9
	$six_seg = [
		["a", "b", "c", "e", "f", "g"],
		["a", "b", "d", "e", "f", "g"],
		["a", "b", "c", "d", "f", "g"],
	];
	$seg = count($wire) === 5 ? $five_seg : $six_seg;

	foreach ($seg as $marks)
		if (!empty($result = mark($segments, $marks, $wire)))
			$next_wires[] = $result;
}

function	mark(array $segments, array $marks, array $wire): array
{
	foreach ($marks as $seg)
	{
		$intersect = array_intersect($segments[ $seg ], $wire);

		if (empty($intersect))
			return [];
		elseif (count($intersect) === 1)
			foreach (array_keys($segments) as $k)
				$segments[ $k ] = array_diff($segments[ $k ], $intersect);
		$segments[ $seg ] = $intersect;
	}
	return $segments;
}

function	prep(string $data): array
{
	$journal = [];

	foreach (array_filter(explode("\n", $data)) as $no => $entries)
		foreach (explode("|", $entries) as $entry)
			$journal[ $no ][] = array_map('trim', array_filter(explode(" ", $entry)));
	return $journal;
}
