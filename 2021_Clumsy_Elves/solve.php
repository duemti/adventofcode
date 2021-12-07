<?PHP
 
if ($argc !== 2)
	die("Error: Please provide input file.\n");

$data = file($argv[1]);
if ($data === false)
	die("Error: Invalid file.\n");
$data = array_filter(array_map('trim', $data));

// PART 1
// PART 2

function	part_one(array $data)
{
}

function	part_two(array $data)
{
}
