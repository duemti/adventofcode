<?PHP
error_reporting(E_ALL);

require __DIR__.'/Battle.php';

function	prepare($input)
{
	$ret = [];
	$input = explode("\n", $input);
	$regex = "/(\d+) units each with (\d+) hit points (?:\((.+)\) )?with an attack that does (\d+) (\b[a-z]+\b) damage at initiative (\d+)/";

	foreach ($input as $line) {
		if (empty($line))
			continue;
		if ($line === "Immune System:" || $line === "Infection:") {
			$sub = $line;
			$id = 0;
			continue;
		}
		
		if (preg_match($regex, $line, $match))
		{
			$group = [
				'id'			=> $id++,
				'units'			=> (int) $match[1],
				'hit-points'	=> (int) $match[2],
				'attack'		=> (int) $match[4],
				'attack-type'	=> $match[5],
				'initiative'	=> (int) $match[6],
				'group'			=> ($sub == "Immune System:" ? "immuneSystem" : "infection"),
			];
			$match = explode(';', $match[3]);
			$weak = trim(strpos($match[0], "weak") !== false ? substr($match[0], 8) : (isset($match[1]) && strpos($match[1], "weak") !== false ? substr($match[1], 8) : ""));
			$immune = trim(strpos($match[0], "immune") !== false ? substr($match[0], 10) : (isset($match[1]) && strpos($match[1], "immune") !== false ? substr($match[1], 10) : ""));

			$group['immune'] = array_map('trim', explode(',', $immune));
			$group['weak'] = array_map('trim', explode(',', $weak));
			$ret[$sub][] = $group;
		}
		else {
			echo $line.PHP_EOL;
			die("Error processing input.\n");
		}
	}
	return $ret;
}

function	solve_first($army)
{
	$battle = new Battle();
	$battle->createArmies($army['Immune System:'], $army['Infection:']);
	$result = $battle->finishWar();
	return "Immune System:\t\e[32m". $result['immuneSystem']. "\e[0m\n"
		."Infection:\t\e[32m". $result['infection']. "\e[0m";
}

function	solve_second($army)
{
	$battle = new Battle();
	$boostBy = 10000;
	$boost = 0;

	while (true)
	{
		$battle->createArmies($army['Immune System:'], $army['Infection:']);
		$boost += $boostBy;
		$battle->boost('immuneSystem', $boost);
		$result = $battle->perform();
		if ($result['winner'] === 'draw')
			echo "draw";
		if ($result['winner'] === 'immuneSystem')
		{
			if ($boostBy != 1)
				$boost = ($boost - $boostBy) - ($boostBy = (int) ($boostBy / 2));
			else
				break ;
		}
	}
	return "Immune System:\t\e[32m". $result['immuneSystem']. "\e[32m\n"
		."Infection:\t\e[32m". $result['infection']. "\e[0m with ". $boost. " boost.";
}

function	solve($input)
{
	echo solve_first($input).PHP_EOL;
	//echo solve_second($input).PHP_EOL;
}

$input = file_get_contents($argv[1]);
$input = prepare($input);
solve($input).PHP_EOL;
