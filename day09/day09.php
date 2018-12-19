<?PHP

function marble_game($players, $marbles, $score)
{
  $points = array_fill(1, $players, 0);
  $player = 0;
  $current_marble = 0;
  $circle = [$current_marble];
  $pos = 0;

  while (++$current_marble <= $marbles) {
    if (++$player > $players)
      $player = 1;



    if ($current_marble % 23 == 0) {
      $points[$player] += $current_marble;
      $pos -= 7;
      if ($pos < 0)
        $pos = count($circle) + $pos;

      $points[$player] += $circle[$pos];
      array_splice($circle, $pos, 1);
      if (!isset($circle[$pos]))
        $pos = 0;

    }
    else
    {
      if (!isset($circle[++$pos]))
        $pos = 0;
      ++$pos;
      array_splice($circle, $pos, 0, [$current_marble]);
    }
  }
  echo $players." players; last marble is worth ".$marbles." points: the highest score is \033[0;32m".max($points)."\033[0m should be ".$score."\n";
}

function digest_input($input)
{
  $ret = [];
  $regex = "/(\d*) players; last marble is worth (\d*) points: high score is (\d*)/";

  foreach ($input as $in) {
    if (preg_match($regex, $in, $result)) {
      $ret[] = ['players' => (int)$result[1], 'marbles' => (int)$result[2], 'score' => (int)$result[3]];
    }
  }
  return $ret;
}

if ($argc != 2) {
  echo "Usage: ".$argv[0]." [input file]\n";
} else {
  $input = file_get_contents($argv[1]);
  if (!$input)
  {
    echo "Failed to open ".$argv[1]."\n";
  }
  else {
    $input = explode("\n", $input);
    $input = digest_input($input);
    echo "Part 1:\n";
    $start = microtime(true);
    foreach ($input as $game) {
      marble_game($game['players'], $game['marbles'], $game['score']);
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";
    echo "\nPart 2:\n";
    echo "Please be patient, the marbles are multiplied by 100!";
    $start = microtime(true);
    foreach ($input as $game) {
      marble_game($game['players'], $game['marbles'] * 100, "?");
    }
    echo "Done in ".microtime(true) - $start;" sec.\n";
  }
}
