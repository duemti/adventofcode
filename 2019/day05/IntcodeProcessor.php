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
				// Intcode Opcodes.
				case 1: // Addition opcode.
					$this->add($this->ip, $param_modes);
					$this->ip += 4;
					break;
				case 2: // Multiplication opcode.
					$this->mult($this->ip, $param_modes);
					$this->ip += 4;
					break;
				case 3: // Opcode for requesting single integer.
					$this->getIntegerInput($this->ip + 1, $param_modes);
					$this->ip += 2;
					break;
				case 4: // Opcode for outputing value at.
					$this->printFromMemory($this->ip + 1, $param_modes);
					$this->ip += 2;
					break;
				case 99:
					return $this->halt();
				default:
					die("\e[31mError:\e[0m Invalid opcode: [". $opcode."]\n");
			}
		}
	}

	private function	getFromMemory(int $address)
	{
		return intval($this->memory[$address]);
	}

	private function	insertInMemory(int $address, int $value)
	{
		$this->memory[$address] = $value;
	}

	// Request an integer input from the user and stores it at $address in memmory.
	private function	getIntegerInput(int $address, array $param_modes)
	{
		while (1)
		{
			$user_input = readline("Input (integer): ");
			if (is_numeric($user_input))
				break;
			echo "\e[31mError:\e[0m [$user_input] - Not an integer.\n";
		}
		// Position Mode parameter.
		$address = $this->getFromMemory($address);
		$this->insertInMemory($address, intval($user_input));
	}

	private function	printFromMemory(int $address, array $param_modes)
	{
		if (!isset($param_modes[0]) || $param_modes[0] === 0)
			$address = $this->getFromMemory($address);
		$output = strval($this->getFromMemory($address));
		$this->output[] = $output;
		echo $output;
	}

	private function	mult(int $addr, array $param_modes)
	{
		$a = $this->getFromMemory($addr + 1);
		if (!isset($param_modes[0]) || $param_modes[0] === 0)
			$a = $this->getFromMemory($a);
		$b = $this->getFromMemory($addr + 2);
		if (!isset($param_modes[1]) || $param_modes[1] === 0)
			$b = $this->getFromMemory($b);
		// Position Mode.
		$c = $this->getFromMemory($addr + 3);
		$this->insertInMemory($c, $a * $b);
	}

	private function	add($addr, $param_modes)
	{
		$a = $this->getFromMemory($addr + 1);
		if (!isset($param_modes[0]) || $param_modes[0] === 0)
			$a = $this->getFromMemory($a);
		$b = $this->getFromMemory($addr + 2);
		if (!isset($param_modes[1]) || $param_modes[1] === 0)
			$b = $this->getFromMemory($b);
		// Position Mode.
		$c = $this->getFromMemory($addr + 3);
		$this->insertInMemory($c, $a + $b);
	}

	private function	halt()
	{
		//file_put_contents('output.txt', implode(",", $this->memory));
		return $this->output;
	}
}
