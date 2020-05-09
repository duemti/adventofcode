<?PHP
ini_set('memory_limit', '1G');

if ($argc != 2)
	exit("Usage:\n\t$argv[0] [file]\n");

$input = trim(file_get_contents($argv[1]));

solve(str_split($input));

solve(str_split( str_repeat($input, 10000) ));


function	solve(array $input)
{
	$phases = 0;

	$fft = SplFixedArray::fromArray($input);

	while ($phases < 100)
	{
echo "*";
		$fft = phase($fft);
		$phases++;
	}
$input = $fft->toArray();
	echo implode($input), PHP_EOL;
	$first = intval(implode(array_slice($input, 0, 7)));
	echo "After $phases phases, the first 8 digits are: \e[32m$first\e[0m\n";

	$second = intval(implode( array_slice($input, $first, 8) ));
	echo "The 8-digit message embedded in the finel output is: \e[32m$second\e[0m\n";
}

function	phase(SplFixedArray $input): SplFixedArray
{
	$size = $input->getSize();
	$new_input = SplFixedArray::fromArray([ ($size - 1) => $input[$size - 1] ]);

	for ($pos = $size - 2; $pos >= 0; $pos--)
	{
		if ($pos > $size / 2)
			$result = $input->offsetGet($pos) + $new_input->offsetGet($pos + 1);
		else
		{
			$result = 0;
			$pattern = 1;
			for ($i = $pos + 1; $i <= $size; $i += 2 * ($pos + 1))
			{
				for ($count = $i - $pos; $count >= 0; $count--)
					$input->next();

				for ($j = $i - 1; $j < $i + $pos && $j < $size; $j++)
				{
					$result += $input->current() * $pattern;
					$input->next();
				}
				$pattern *= -1;
			}
		}
		$new_input->offsetSet($pos, abs($result % 10));
	}
	return $new_input;
}
