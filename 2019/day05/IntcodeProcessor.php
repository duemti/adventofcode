<?PHP

class	IntcodeProcessor
{
	private $ip = 0;
	private $program = null;
	private $memory = null;
	private $output = [];

	/**
	 * How the parameters should be interpreted.
	 *
	 * 0 -> position mode, the value indicated address in memory.
	 * 1 -> immediate mode, value is interpreted as is.
	 */
	private $parameter_mode = 0;

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
			$opcode = strval($this->memory[$this->ip]);
			$param_modes = array_reverse(array_map('intval', str_split(substr($opcode, 0, -2))));
			switch (intval(substr($opcode, -2)))
			{
				case 1: // Addition opcode.
					$this->add($param_modes);
					break;
				case 2: // Multiplication opcode.
					$this->mult($param_modes);
					break;
				case 3: // Opcode for requesting single integer.
					$this->getIntegerInput();
					break;
				case 4: // Opcode for outputing value at.
					$this->printFromMemory($param_modes);
					break;
				case 5:
					$this->jumpIfTrue($param_modes);
					break;
				case 6:
					$this->jumpIfFalse($param_modes);
					break;
				case 7:
					$this->lessThan($param_modes);
					break;
				case 8:
					$this->equals($param_modes);
					break;
				case 99:
					return $this->halt();
				default:
					die("\e[31mError:\e[0m Invalid opcode: [". $opcode."] at [".$this->ip."]\n");
			}
		}
	}

	private function	equals(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;
		$a = $this->getFromMemory($this->ip + 1, $pmode[0]);
		$b = $this->getFromMemory($this->ip + 2, $pmode[1]);
		$c = $this->getFromMemory($this->ip + 3, 1);
		$this->insertInMemory($c, $a === $b ? 1 : 0);
		$this->ip += 4;
	}

	private function	lessThan(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;

		$this->insertInMemory(
			$this->getFromMemory($this->ip + 3, 1),
			$this->getFromMemory($this->ip + 1, $pmode[0])
			<
			$this->getFromMemory($this->ip + 2, $pmode[1])
			? 1 : 0
		);
		$this->ip += 4;
	}

	private function	jumpIfFalse(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;
		
		if ($this->getFromMemory($this->ip + 1, $pmode[0]) === 0)
			$this->ip = $this->getFromMemory($this->ip + 2, $pmode[1]);
		else
			$this->ip += 3;
	}

	private function	jumpIfTrue(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;

		if ($this->getFromMemory($this->ip + 1, $pmode[0]))
			$this->ip = $this->getFromMemory($this->ip + 2, $pmode[1]);
		else
			$this->ip += 3;
	}

	private function	getFromMemory(int $address, int $mode = 0)
	{
		$value = intval($this->memory[$address]);
		return ($mode === 0 ? intval($this->memory[$value]) : $value);
	}

	private function	insertInMemory(int $address, int $value)
	{
		$this->memory[$address] = $value;
	}

	// Request an integer input from the user and stores it at $address in memmory.
	private function	getIntegerInput()
	{
		while (!is_numeric($user_input = readline("Input (integer): ")))
			echo "\e[31mError:\e[0m [$user_input] - Not an integer.\n";

		// Position Mode parameter.
		$this->insertInMemory(
			$this->getFromMemory($this->ip + 1, 1),
			intval($user_input)
		);
		$this->ip += 2;
	}

	private function	printFromMemory(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$output = strval($this->getFromMemory($this->ip + 1, $pmode[0]));
		$this->output[] = $output;
		echo $output;
		$this->ip += 2;
	}

	private function	mult(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;

		$this->insertInMemory(
			$this->getFromMemory($this->ip + 3, 1),
			$this->getFromMemory($this->ip + 1, $pmode[0]) *
			$this->getFromMemory($this->ip + 2, $pmode[1])
		);
		$this->ip += 4;
	}

	private function	add(array $pmode)
	{
		$pmode[0] = isset($pmode[0]) ? $pmode[0] : 0;
		$pmode[1] = isset($pmode[1]) ? $pmode[1] : 0;

		$this->insertInMemory(
			$this->getFromMemory($this->ip + 3, 1),
			$this->getFromMemory($this->ip + 1, $pmode[0]) +
			$this->getFromMemory($this->ip + 2, $pmode[1])
		);
		$this->ip += 4;
	}

	private function	halt()
	{
		//file_put_contents('output.txt', implode(",", $this->memory));
		return $this->output;
	}
}
