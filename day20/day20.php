<?PHP

if ($argc != 2)
	die("Please supply a file for input.");

$inpur = file_get_contents($argv[1]);
if ($input === false)
	die("Can't open the file.");


