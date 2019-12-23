<?PHP

class	IntcodeProcessor
{
	private $ip = 0;
	private $program = null;
	private $memory = null;

	public function	__construct(array $program)
	{
		$this->program = $program;
	}

	public function	initMemory(array $program = null)
	{
		$this->ip = 0;
		$this->memory = $program ?: $this->program;
	}

	public function	setNoun(int $value)
	{
		$this->program[1] = $value;
	}

	public function	setVerb(int $value)
	{
		$this->program[2] = $value;
	}

	public function	run()
	{
		while (1)
		{
			$opcode = $this->getInt($this->ip);
			switch ($opcode)
			{
				// Addition intcode.
				case 1:
				case 2:
					$this->exec($opcode, $this->getInt($this->ip + 1), $this->getInt($this->ip + 2), $this->getInt($this->ip + 3));
					break;
				case 99:
					return $this->halt();
				default:
					die("Invalid opcode: ". $opcode);
			}
		}
	}

	private function	getInt($at)
	{
		return intval($this->memory[$at]);
	}
	
	private function	exec(int $opcode, int $a, int $b, int $c)
	{
		switch ($opcode)
		{
			case 1:
				$this->memory[$c] = $this->getInt($a) + $this->getInt($b);
				break;
			case 2:
				$this->memory[$c] = $this->getInt($a) * $this->getInt($b);
				break;
		}
		$this->ip += 4;
	}

	private function	halt()
	{
		file_put_contents('output.txt', implode(",", $this->memory));
		return $this->memory[0];
	}
}

$input = file_get_contents($argv[1]);

// Part One.
$program = new IntcodeProcessor(explode(',', $input));
$program->setNoun(12);
$program->setVerb(2);
$program->initMemory();
echo "Result: \e[32m", $program->run(), "\e[0m\n";

// Part Two.
for ($noun = 0; $noun <= 99; $noun++)
{
	$program->setNoun($noun);
	for ($verb = 0; $verb <= 99; $verb++)
	{
		$program->setVerb($verb);
		$program->initMemory();
		if ($program->run() === 19690720) {
			echo "Result: \e[32m", (100 * $noun + $verb), "\e[0m (Noun: $noun, Verb: $verb)\n";
			return;
		}
	}
}
echo "END.\n";
