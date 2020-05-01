<?PHP
include_once __DIR__.'/IntcodeProcessor.php';
include_once __DIR__.'/AmplifierControllerSoftware.php';

$input = file_get_contents($argv[1]);


function	permutation(array $config, int $k, array &$perm)
{
	if ($k === 4)
		$perm[] = $config;

	for ($i = $k; $i < 5; $i++)
	{
		$tmp = $config;
		$tmp[$k] = $config[$i];
		$tmp[$i] = $config[$k];
		permutation($tmp, $k + 1, $perm);
	}
}

$configurations = [];
permutation([0, 1, 2, 3, 4], 0, $configurations);
$acs = new AmplifierControllerSoftware($configurations, array_map('intval', explode(',', $input)));
$acs->run();
echo "\nThe highest signal that can be sent to thrusters: \e[32m", $acs->highest_signal, "\e[0m\n";
unset($acs);

$configurations = [];
permutation([5, 6, 7, 8, 9], 0, $configurations);
$acs = new AmplifierControllerSoftware($configurations, array_map('intval', explode(',', $input)), true);
$acs->run();
echo "\nThe highest signal that can be sent to thrusters (with Feedback Loop): \e[32m", $acs->highest_signal, "\e[0m\n";
unset($acs);

