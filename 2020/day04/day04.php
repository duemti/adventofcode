<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = array_map("my_split",
	explode("\n\n",
	file_get_contents($file)
));

echo "Part 1: There are \e[32m", part_one($input), "\e[0m valid passports.\n";
echo "Part 2: There are \e[32m", part_two($input), "\e[0m valid passports with valid fields.\n";

// Validate all fields + optional 'cid' field.
function	part_two(array $input): int
{
	$valid = 0;

	foreach ($input as $passport)
	{
		if (isset($passport['byr']) &&
			strlen($passport['byr']) === 4 &&
			intval($passport['byr']) >= 1920 &&
			intval($passport['byr']) <= 2002
			&&
			isset($passport['iyr']) &&
			strlen($passport['iyr']) === 4 &&
			intval($passport['iyr']) >= 2010 &&
			intval($passport['iyr']) <= 2020
			&&
			isset($passport['eyr']) &&
			strlen($passport['eyr']) === 4 &&
			intval($passport['eyr']) >= 2020 &&
			intval($passport['eyr']) <= 2030
			&&
			isset($passport['hgt']) && validate_height($passport['hgt'])
			&&
			isset($passport['hcl']) &&
			preg_match("/^#[0-9a-f]{6}$/", $passport['hcl'])
			&&
			isset($passport['ecl']) &&
			preg_match("/^(amb|blu|brn|gry|grn|hzl|oth)$/", $passport['ecl'])
			&&
			isset($passport['pid']) &&
			preg_match("/^[0-9]{9}$/", $passport['pid'])
		)
			$valid++;
	}
	return $valid;
}

function	validate_height(string $hgt): bool
{
	$matches = [];

	if (preg_match("/^(\d+)(cm|in)$/", $hgt, $matches) &&
		(($matches[2] === "cm" && $matches[1] >= 150 && $matches[1] <= 193) ||
		($matches[2] === "in" && $matches[1] >= 59 && $matches[1] <= 76)))
		return true;
	return false;
}

// Vlaidate number of fileds + optional 'cid' field.
function	part_one(array $input): int
{
	$valid = 0;

	foreach ($input as $passport)
	{
		$numOfFields = count($passport);

		if ($numOfFields === 8 ||
			($numOfFields === 7 && !isset($passport['cid'])))
			$valid++;
	}
	return $valid;
}

// Format the input.
function	my_split(string $info): array
{
	$allFields = [];

	foreach (preg_split("/\s+/", $info, -1, PREG_SPLIT_NO_EMPTY) as $field)
	{
		$f = explode(":", $field);
		$allFields[$f[0]] = $f[1];
	}
	return $allFields;
}
