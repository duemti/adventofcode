<?PHP

function find_range($clay_veins)
{
	$minx = 500;
	$maxx = 500;
	$maxy = 1;

	foreach ($clay_veins as $cv) {
		if ($cv['x'] < $minx)
			$minx = $cv['x'];

		if ($cv['x'] > $maxx)
			$maxx = $cv['x'];

		if (!isset($miny) || $cv['y'] < $miny)
			$miny = $cv['y'];

		if ($cv['y'] > $maxy)
			$maxy = $cv['y'];
	}
	return ['miny' => $miny, 'maxy' => $maxy, 'minx' => $minx, 'maxx' => $maxx];
}

// Check if water can flow.
function	check_if_water_can_flow($x, $y, &$clay_veins, &$water_at_rest)
{
	$coor = array(
		'x' => $x,
		'y' => $y
	);

	if (in_array($coor, $clay_veins) || in_array($coor, $water_at_rest))
		return false;
	return true;
}

function	flow(&$springs_coor, &$springs_root, $clay_veins, $water_at_rest, $maxy)
{
	$add_to_springs = [];


	$springs = &$springs_root["coor"];
	$spring = $springs[0];

	if ($springs_root["can_flow"] === false)
		return false;
	// Flow downwards.
	if (check_if_water_can_flow($spring['x'], ++$spring['y'], $clay_veins, $water_at_rest)) {
		if ($spring['y'] > $maxy) {
			$springs_root["can_flow"] = false;
			return false;
		}
		$springs_root["direction"] = "down";
		array_unshift($springs, $spring);
		array_push($springs_coor, $spring);
	}
	// Flow horizontally.
	else {
		$spring['y']--;
		$spring['x']--;
		// Flow left.
		if ($springs_root["direction"] != "right") {
			if (check_if_water_can_flow($spring['x'], $spring['y'], $clay_veins, $water_at_rest)) {

				if ($springs_root["direction"] == "down") {
					$springs_root["left"] = array(
						"coor" => [$spring],
						"left" => [],
						"right" => [],
						"can_flow" => true,
						"direction" => "left"
					);
				} else
					array_unshift($springs, $spring);

				array_push($springs_coor, $spring);
			}
			else if ($springs_root["direction"] == "left")
				$springs_root["can_flow"] = false;
		}

		$spring['x']++;
		$spring['x']++;
		// Flow right.
		if ($springs_root["direction"] != "left") {
			if (check_if_water_can_flow($spring['x'], $spring['y'], $clay_veins, $water_at_rest)) {

				if ($springs_root["direction"] == "down") {
					$springs_root["right"] = array(
						"coor" => [$spring],
						"left" => [],
						"right" => [],
						"can_flow" => true,
						"direction" => "right"
					);
				} else
					array_unshift($springs, $spring);

				array_push($springs_coor, $spring);
			}
			else if ($springs_root["direction"] == "right")
				$springs_root["can_flow"] = false;
		}
	}
	return true;
}

function	recursive_flow(&$water_flow_allcoor, &$water_flow, &$clay_veins, &$water_at_rest, &$maxy)
{
	if (empty($water_flow))
		return false;
	if (false == $water_flow["can_flow"])
		return false;


	$wf = $water_flow;
/*	if ($wf["direction"] == "left" && !empty($wf["left"]) && $wf["left"]["can_flow"] === false)
		$water_flow["can_flow"] = false;
	if ($wf["direction"] == "right" && !empty($wf["right"]) && $wf["right"]["can_flow"] === false)
		$water_flow["can_flow"] = false;
 */
	if ((!empty($water_flow["left"]) && false == $water_flow["left"]["can_flow"]) &&
		(!empty($water_flow["right"]) && false == $water_flow["right"]["can_flow"])) {
		$water_at_rest = array_unique(array_merge($water_at_rest, $water_flow["left"]["coor"], $water_flow["right"]["coor"], [array_shift($water_flow["coor"])]), SORT_REGULAR);
		$water_flow["left"] = [];
		$water_flow["right"] = [];
	}
	else if ((empty($wf["left"]) && (!empty($wf["right"]) && $wf["right"]["can_flow"] === false) )) {
		$water_at_rest = array_unique(array_merge($water_at_rest, $water_flow["right"]["coor"], [array_shift($water_flow["coor"])] ), SORT_REGULAR);
		$water_flow["right"] = [];
	}
	else if (empty($wf["right"]) && (!empty($wf["left"]) && $wf["left"]["can_flow"] === false) ) {
		$water_at_rest = array_unique(array_merge($water_at_rest, $water_flow["left"]["coor"], [array_shift($water_flow["coor"])] ), SORT_REGULAR);
		$water_flow["left"] = [];
	}

	if (!empty($water_flow["right"]))
		$one = recursive_flow($water_flow_allcoor, $water_flow["right"], $clay_veins, $water_at_rest, $maxy);

	if (!empty($water_flow["left"]))
		$true = recursive_flow($water_flow_allcoor, $water_flow["left"], $clay_veins, $water_at_rest, $maxy);

	if (isset($one) && isset($two))
		return $one && $two;
	if (isset($one))
		return $one;
	if (isset($two))
		return $two;

echo "threads.\n";
$res = flow($water_flow_allcoor, $water_flow, $clay_veins, $water_at_rest, $maxy);
var_dump($res);
return $res;
}

function	first_part($clay_veins)
{
	$range = find_range($clay_veins);
	$water_at_rest = [];
	$springs = [
		"root_spring" => [
			"coor" => [
				[
					'x' => 499,
					'y' => 0
				]
			],
			"left" => [],
			"right" => [],
			"can_flow" => true,
			"direction" => "down"
		],
		"all_coor" => [
			[
				'x' => 499,
				'y' => 0
			]
		]
	];

	$status = true;
	while ($status) {
		$status = recursive_flow($springs["all_coor"], $springs["root_spring"], $clay_veins, $water_at_rest, $range["maxy"]);
//var_dump($springs['root_spring']);
		print_data($clay_veins, $water_at_rest, $springs["all_coor"], $range);
	}

	return count(array_unique($water_at_rest, SORT_REGULAR)) + count(array_unique($springs, SORT_REGULAR)) - $range['miny'];
}

function	print_data($clay_veins, $water_at_rest, $springs, $range)
{
	for ($y = 0; $y <= $range['maxy'] + 1; $y++) {
		for ($x = $range['minx'] - 1; $x <= $range['maxx'] + 1; $x++) {

			$coor = [
				'y' => $y,
				'x' => $x
			];

			$printed = false;
			if (in_array($coor, $clay_veins)) {
				echo "\e[100m#\e[0m";
				$printed = true;
			}
			if (in_array($coor, $water_at_rest)) {
				echo "\e[0;44m~\e[0m";
				$printed = true;
			}
			else	if (in_array($coor, $springs)) {
					echo "|";
					$printed = true;
				}
			if (!$printed)
				echo ".";
		}
		echo "\n";
	}
	echo "\n";
}


function digest_input($input)
{
	$result = [];
	$regex = "/(x|y)=(\d+), (x|y)=(\d+)..(\d+)/";

	foreach ($input as $row) {
		if (!$row)
			continue;

		if (preg_match($regex, $row, $match)) {
			foreach (range($match[4], $match[5]) as $coor) {
				$result[] = [
					$match[1] => (int)$match[2],
					$match[3] => (int)$coor
				];
			}
		}
	}
	return $result;
}


if ($argc != 2) {
  echo "Usage: ".$argv[0]." [input file]\n";
} else {
  $input = file_get_contents($argv[1]);
  if (!$input)
  {
    echo "Failed to open ".$argv[1]."\n";
  }
  else {
    // Part 1
    echo "Part 1:\n";
	$start = microtime(true);

	// Split the string into array of string's by newline.
	$input = explode("\n", $input);

	// Making sense of input.
	$input = digest_input($input);

	$result = first_part($input);
	echo "Water can reach \e[92m" . $result. "\e[0m square meters.\n";

    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    //echo "\nPart 2:\n";
    //$start = microtime(true);
    //echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
