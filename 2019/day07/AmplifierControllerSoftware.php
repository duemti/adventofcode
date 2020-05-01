<?PHP

include_once __DIR__.'/IntcodeProcessor.php';

class	AmplifierControllerSoftware
{
	private	$amplifiers = [];
	private	$configurations = [];
	private	$feedback_loop = false;

	public	$highest_signal = 0;

	public function	__construct(
		array $configurations,
		array $code,
		bool $feedback_loop = false
	){
		$this->configurations = $configurations;
		$this->feedback_loop = $feedback_loop;

		for ($i = 0; $i < 5; $i++)
			$this->amplifiers[] = new IntcodeProcessor($code, true, $feedback_loop);
	}

	public function	run()
	{
		foreach ($this->configurations as $config)
		{
			foreach ($config as $key => $amplifier_setting)
			{
				$this->amplifiers[$key]->initMemory();
				$this->amplifiers[$key]->input = [$amplifier_setting];
			}
			$signal = $this->exec();
			if ($signal > $this->highest_signal)
				$this->highest_signal = $signal;
		}
	}

	private function	exec()
	{
		$signal = [0, 0, 0, 0, 0];
		$amp = 0;

		while (1)
		{
			$this->amplifiers[$amp]->input[] = $signal[($amp - 1) < 0 ? 4 : $amp - 1];
			$signal[$amp] = $this->amplifiers[$amp]->run();

			if ($this->amplifiers[$amp]->haltCause === "end" && ($this->feedback_loop || $amp === 4))
				break;
			if (++$amp > 4)
				$amp = 0;
		}
		return $signal[4];
	}
}
