<?PHP
include_once __DIR__.'/IntcodeProcessor.php';

$input = file_get_contents($argv[1]);

$program = new IntcodeProcessor(array_map('intval', explode(',', $input)));
$program->initMemory();
$output = $program->run();
echo "\nResult: \e[0m\e[32m", array_pop($output),"\e[0m is the last output before halt.\n";
