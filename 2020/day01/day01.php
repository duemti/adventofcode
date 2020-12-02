<?PHP

$file = isset($argv[1]) ? $argv[1] : "./input.txt";
if (FALSE === file_exists($file))
	die("Error: The is no input file or file doesn't exist.\n");
$input = array_filter(file($file));
$input = array_map('intval', $input);

echo "Part 1: \e[32m", part_one($input), "\e[0m\n";
echo "Part 2: \e[32m", part_two($input), "\e[0m\n";

function	part_one(array $input): int
{
	for ($i = 0; $i < count($input); $i++)
	{
		for ($j = $i + 1; $j < count($input); $j++)
		{
			$a = $input[$i];
			$b = $input[$j];

			if ($a + $b === 2020)
				return $a * $b;
		}
	}
}

function	part_two(array $input, int $pos = 0): int
{
	for ($i = 0; $i < count($input); $i++)
	{
		for ($j = $i + 1; $j < count($input); $j++)
		{
			for ($k = $j + 1; $k < count($input); $k++)
			{
				$a = $input[$i];
				$b = $input[$j];
				$c = $input[$k];

				if ($a + $b + $c === 2020)
					return $a * $b * $c;
			}
		}
	}
}
