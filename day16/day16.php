<?PHP
/*
 * By: dpetrov
 *
 */

function find_opcode($data, $inst_set)
{
	$order = [];

	foreach ($data as $i => $row) {
		$reg = $row['before'];
		$ins = $row['instruction'];
		$opcode = $ins[0];
		$a = $ins[1];
		$b = $ins[2];
		$c = $ins[3];

		$matched = [];

		foreach ($inst_set as $op => $exec) {
			if ($row['after'] == $exec($reg, $a, $b, $c))
				$matched[] = $op;
		}

		if (count($matched) == 1 && !isset($order[$opcode])) {
			$order[$opcode] = current($matched);
			unset($inst_set[current($matched)]);
			reset($data);
		}
	}
	return $order;
}

function run_program($prog, $inst_set, $order)
{
	$register = [0, 0, 0, 0];

	foreach ($prog as $cmd) {
		$inst = $inst_set[ $order[$cmd[0]] ];
		$register = $inst($register, $cmd[1], $cmd[2], $cmd[3]);
	}
	return $register;
}

function second_part($data)
{
	$inst_set = [
		"addr" => function ($reg, int $a, int $b, int $c) { $reg[ $c ] = $reg[ $a ] + $reg[ $b ];  return $reg; },
		"addi" => function ($reg, int $a, int $b, int $c) { $reg[ $c ] = $reg[ $a ] + $b;  return $reg; },
		"mulr" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] * $reg[ $b ];  return $reg; },
		"muli" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] * $b;  return $reg; },
		"banr" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] & $reg[ $b ];  return $reg; },
		"bani" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] & $b;  return $reg; },
		"borr" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] | $reg[ $b ];  return $reg; },
		"bori" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ] | $b;  return $reg; },
		"setr" => function ($reg, $a, $b, $c) { $reg[ $c ] = $reg[ $a ];  return $reg; },
		"seti" => function ($reg, $a, $b, $c) { $reg[ $c ] = $a;  return $reg; },
		"gtir" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($a > $reg[ $b ]) ? 1 : 0;  return $reg; },
		"gtri" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($reg[ $a ] > $b) ? 1 : 0;  return $reg; },
		"gtrr" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($reg[ $a ] > $reg[ $b ]) ? 1 : 0;  return $reg; },
		"eqir" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($a == $reg[ $b ]) ? 1 : 0;  return $reg; },
		"eqri" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($reg[ $a ] == $b) ? 1 : 0;  return $reg; },
		"eqrr" => function ($reg, $a, $b, $c) { $reg[ $c ] = ($reg[ $a ] == $reg[ $b ]) ? 1 : 0;  return $reg; }
	];

	$order = find_opcode($data['data'], $inst_set);

	return run_program($data['program'], $inst_set, $order);
}

function digest_input($input)
{
	$result = ['program' => [], 'data' => []];
	foreach ($input as $row) {
		if (!$row)
			continue;
		if (preg_match("/Before: *\[(\d+), (\d+), (\d+), (\d+)\]\n(\d+) (\d+) (\d+) (\d+)\nAfter: *\[(\d+), (\d+), (\d+), (\d+)\]/", $row, $match)) {
			$result['data'][] = [
				'before'		=> [$match[1], $match[2], $match[3], $match[4]],
				'instruction'	=> [$match[5], $match[6], $match[7], $match[8]],
				'after'			=> [$match[9], $match[10], $match[11], $match[12]]
			];
		}
		else {
			foreach (explode("\n", $row) as $cmd) {
				if (!$cmd)
					continue;
				if (preg_match("/(\d+) (\d+) (\d+) (\d+)/", $cmd, $match))
					$result['program'][] = [(int)$match[1], (int)$match[2], (int)$match[3], (int)$match[4]];
			}
		}
	}
	return $result;
}


if ($argc != 2) {
	echo "Usage: ".$argv[0]." [input file]\n";
} else {
	$input = file_get_contents($argv[1]);
	if (!$input) {
	echo "Failed to open ".$argv[1]."\n";
	}
	else {
		$start = microtime(true);
		$input = digest_input(explode("\n\n", $input));
		$result = second_part($input);
		echo "Register 1: \033[0;32m".$result[0]."\033[0m\n";
		echo "Register 2: \033[0;32m".$result[1]."\033[0m\n";
		echo "Register 3: \033[0;32m".$result[2]."\033[0m\n";
		echo "Register 4: \033[0;32m".$result[3]."\033[0m\n";
		echo "Done in ".(microtime(true) - $start)." sec.\n";
	}
}
