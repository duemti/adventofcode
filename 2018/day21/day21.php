<?PHP

function	solve()
{
	$r0 = 0;
	$r1 = 0;
	$r2 = 0;
	$r3 = 0;
	$r4 = 0;
	$r5 = 0;
	$lowest = [];

	$r2 = 123;
	do {
		$r2 &= 456;
	} while ($r2 != 72);
	$r2 = 0;
	do {
		$r4 = $r2 | 65536;
		$r2 = 6718165;
		while (1) {
			$r3 = $r4 & 255;
			$r2 = ((($r2 + $r3) & 16777215) * 65899) & 16777215;
			if (256 > $r4)
				break;
			$r3 = 0;
			while (1) {
				$r1 = ($r3 + 1) * 256;
				if ($r1 > $r4)
					break;
				$r3++;
			}
			$r4 = $r3;
		}
		if (!in_array($r2, $lowest))
			$lowest[] = $r2;
		else {
			echo "Found Duplicate! halting...\n", array_shift($lowest), "\n", array_pop($lowest);
			break;
		}
	} while ($r2 != $r0);


}


// Main entry into the program.
if ($argc != 2) {
	echo "Usage: ".$argv[0]." [file]\n\n";
	echo "Options:\n";
	echo "Arguments:\n";
	echo "\tfile - File for input.\n";
	return ;
}

$file = $argv[1];

$input = file_get_contents($file);
if ($input === false)
	die("Failed to open ".$file."\n");

echo "Solving...\n";
// Solving...
$result = solve($input);

