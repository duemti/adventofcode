<?PHP

include_once __DIR__.'/IntcodeProcessor.php';

$boost_program = explode(",", file_get_contents($argv[1]));
$proc = new IntcodeProcessor($boost_program);
$result = $proc->run();
