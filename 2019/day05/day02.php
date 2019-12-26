<?PHP
include_once __DIR__.'/IntcodeProcessor.php';

$input = file_get_contents($argv[1]);

// Part One.
$program = new IntcodeProcessor(array_map('intval', explode(',', $input)));
$program->initMemory();
$program->run();
echo "Result: \e[0m\e[32m", "\e[0m\n";

// Part Two.
