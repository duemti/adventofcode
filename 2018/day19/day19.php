<?PHP

function	digest_input($input)
{
	$dinput = [];

	foreach (explode("\n", $input) as $row) {
		if ($row)
			array_push($dinput, explode(" ", $row));
	}
	return $dinput;
}

function	execute_line($line, &$ip, &$registers)
{
	switch ($line[0]) {
		case "setr":
			$registers[$line[3]] = $registers[$line[1]];
			break;
		case "seti":
			$registers[$line[3]] = $line[1];
			break;
		case "addr":
			$registers[$line[3]] = $registers[$line[1]] + $registers[$line[2]];
			break;
		case "addi":
			$registers[$line[3]] = $registers[$line[1]] + $line[2];
			break;
		case "mulr":
			$registers[$line[3]] = $registers[$line[1]] * $registers[$line[2]];
			break;
		case "muli":
			$registers[$line[3]] = $registers[$line[1]] * $line[2];
			break;
		case "banr":
			$registers[$line[3]] = $registers[$line[1]] & $registers[$line[2]];
			break;
		case "bani":
			$registers[$line[3]] = $registers[$line[1]] & $line[2];
			break;
		case "borr":
			$registers[$line[3]] = $registers[$line[1]] | $registers[$line[2]];
			break;
		case "bori":
			$registers[$line[3]] = $registers[$line[1]] | $line[2];
			break;
		case "gtir":
			$registers[$line[3]] = ($line[1] > $registers[$line[2]]) ? 1 : 0;
			break;
		case "gtri":
			$registers[$line[3]] = ($registers[$line[1]] > $line[2]) ? 1 : 0;
			break;
		case "gtrr":
			$registers[$line[3]] = ($registers[$line[1]] > $registers[$line[2]]) ? 1 : 0;
			break;
		case "eqir":
			$registers[$line[3]] = ($registers[$line[2]] == $line[1]) ? 1 : 0;
			break;
		case "eqri":
			$registers[$line[3]] = ($registers[$line[1]] == $line[2]) ? 1 : 0;
			break;
		case "eqrr":
			$registers[$line[3]] = ($registers[$line[1]] == $registers[$line[2]]) ? 1 : 0;
			break;
		default:
			echo "\e[31mNo such instruction: ".$line[0]."\e[0m";
			return false;
	}
	return true;
}

function	solve($input, $registers)
{
	$ip = 0;

	// Pre-processing
	if ($input[0][0] == "#ip") {
		$ip_reg = intval($input[0][1]);
		array_shift($input);
	}

	// Executing
	while (1) {
		if (isset($ip_reg))
			$registers[$ip_reg] = $ip;
		execute_line($input[$ip], $ip, $registers);

		if (isset($ip_reg)) {
			$ip = $registers[$ip_reg];
			if ($ip < 0 || $ip >= count($input))
				break;
		}
		$ip++;
		if ($ip < 0 || $ip >= count($input))
			break;
	}
	return $registers;
}

function	solve_part_2()
{
	$r0 = 1;
	$r1 = 0;
	$r2 = 0;
	$r3 = 0;
	$r4 = 0;
	$r5 = 0;

	$r3 += 2;
	$r3 *= $r3;
	$r3 *= 19;
	$r3 *= 11;
	$r4 += 8;
	$r4 *= 22;
	$r4 += 13;
	$r3 += $r4;

	$r4 = 27;
	$r4 *= 28;
	$r4 += 29;
	$r4 *= 30;
	$r4 *= 14;
	$r4 *= 32;
	$r3 += $r4;
	$r0 = 0;

	$r1 = 1;
	while (1) {
		$r2 = 1;
		$r4 = $r1 * $r3;
		if ($r3 % $r1 == 0 && $r3 / $r1 >= $r2)
			$r0 += $r1;
		$r2 = $r3 + 1;
		$r1++;
		if ($r1 > $r3)
			break;
	}

	return [$r0, $r1, $r2, $r3, $r4, 16 * 16];
}

if ($argc != 2) {
	echo "Usage: ".$argv[0]." [file]\n\n";
	echo "Options:\n";
	echo "Arguments:\n";
	echo "\tfile - File for input.\n";
} else {
	$file = $argv[1];

	$input = file_get_contents($file);
	if ($input === false)
    	die("Failed to open ".$file."\n");

	echo "Solving...\n";
	// Making sense of input.
	$input = digest_input($input);
	$result = solve($input, [0, 0, 0, 0, 0, 0]);
	echo "Value in register 0: \e[32m".$result[0]."\e[0m\n";

	echo "Starting with value 1 in register 0.\n";
	$result = solve_part_2();
	echo "Value in register 0: \e[32m".$result[0]."\e[0m\n";
}
