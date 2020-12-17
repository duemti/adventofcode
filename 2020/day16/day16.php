<?PHP

require_once __DIR__."/test.php";

$file = isset($argv[1]) ? $argv[1] : "./puzzle.txt";

// Run tests...
if (in_array("-t", $argv))
	run_tests();

if (FALSE === file_exists($file))
	die("Error: There is no input puzzle file or file doesn't exist.\n");
$puzzle = read(file_get_contents($file));

$valid_tickets = [];
echo "Part 1: \e[32m", part_one($puzzle, $valid_tickets), "\e[0m\n";
echo "Part 2: \e[32m", part_two($puzzle, $valid_tickets), "\e[0m\n";

function	part_one(array $puzzle, array &$valid_tickets): int
{
	$invalid = 0;

	foreach ($puzzle['nearby_tickets'] as $ticket)
	{
		$invalid_ticket = false;

		foreach ($ticket as $field_num)
		{
			$valid = false;

			// Ckecking the field validity.
			foreach ($puzzle['fields'] as $fields)
				foreach ($fields as $rule)
						if ($rule[0] <= $field_num && $field_num <= $rule[1])
							$valid = true;

			if (!$valid)
			{
				$invalid_ticket = true;
				$invalid += $field_num;
			}
		}
		if (false === $invalid_ticket)
			$valid_tickets[] = $ticket;
	}
	return $invalid;
}

function	part_two(array $puzzle, array $valid_tickets): int
{
	$match_fields = array_fill(0, count($puzzle['fields']), array_keys($puzzle['fields']));
	foreach ($valid_tickets as $ticket)
	{
		foreach ($ticket as $pos => $field_num)
		{
			// Ckecking the field validity.
			foreach (array_keys($match_fields[$pos]) as $id)
			{
				$name = $match_fields[$pos][$id];
				$match = false;

				foreach ($puzzle['fields'][$name] as $rule)
					if ($rule[0] <= $field_num && $field_num <= $rule[1])
						$match = true;
				if ($match === false)
					unset($match_fields[$pos][$id]);
			}
		}
	}

	$final = [];
	while (!empty($match_fields))
	{
		foreach (array_keys($match_fields) as $i)
		{
			$field = $match_fields[$i];

			if (count($field) === 1)
			{
				$final[$i] = array_pop($field);
				unset($match_fields[$i]);
			}
			else
				foreach ($field as $k => $v)
					if (in_array($v, $final, true))
						unset($match_fields[$i][$k]);
		}
	}

	$result = [];
	foreach ($final as $k => $v)
		if (strncmp($v, "departure", 9) === 0)
			$result[] = $puzzle['my_ticket'][$k];
	return array_product($result);
}

function	read(string $file): array
{
	$fields = [];

	$puzz = array_filter(explode("\n\n", $file));
	foreach (array_filter(explode("\n", $puzz[0])) as $fields)
	{
		$m = [];
		preg_match("/^(.+): (\d+)-(\d+) or (\d+)-(\d+)$/", $fields, $m);
		$puzzle['fields'][$m[1]] = [ [intval($m[2]), intval($m[3])], [intval($m[4]), intval($m[5])] ];
	}
	$puzzle['my_ticket'] = array_map('intval', array_filter(explode(",", explode("\n", $puzz[1])[1])));

	foreach (array_filter(explode("\n", $puzz[2])) as $k => $nt)
	{
		if (!$k)
			continue;

		$puzzle['nearby_tickets'][] = array_map('intval',
										array_filter(
											explode(",", $nt)));
	}
	return $puzzle;
}
