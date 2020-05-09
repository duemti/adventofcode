<?PHP
ini_set('memory_limit', '1G');

if ($argc != 2)
	exit("Usage:\n\t$argv[0] [file]\n");

$input = trim(file_get_contents($argv[1]));

$digits = solve(str_split($input));
echo "After 100 phases, the first 8 digits are: \e[32m$digits\e[0m\n";

$digits = solve(str_split( str_repeat($input, 10000) ), intval(substr($input, 0, 7)));
echo "The 8-digit message embedded in the finel output is: \e[32m$digits\e[0m\n";

function	solve(array $input, int $offset = 0): string
{
	$fft = SplFixedArray::fromArray($input);
	$phases = 0;

	while ($phases++ < 100)
		$fft = phase($fft, $offset);

	$res = "";
	for ($i = 0; $i < 8; $i++)
		$res .= (string)$fft->offsetGet($offset + $i);
	return $res;
}

function	phase(SplFixedArray $input, int $offset): SplFixedArray
{
	$size = $input->getSize();
	$new_input = SplFixedArray::fromArray([ ($size - 1) => $input[$size - 1] ]);

	for ($pos = $size - 2; $pos >= 0 + $offset; $pos--)
	{
		if ($pos > $size / 2)
			$result = intval($input->offsetGet($pos) + $new_input->offsetGet($pos + 1));
		else
		{
			$result = 0;
			$pattern = 1;
			for ($i = $pos + 1; $i <= $size; $i += 2 * ($pos + 1))
			{
				for ($j = $i - 1; $j < $i + $pos && $j < $size; $j++)
					$result += intval($input->offsetGet($j) * $pattern);
				$pattern *= -1;
			}
		}
		$new_input->offsetSet($pos, abs($result % 10));
	}
	return $new_input;
}
