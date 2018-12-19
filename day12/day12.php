<?PHP

function calibrate($state, &$offset)
{
  $pos = 0;
  while ($state[$pos] == '.')
    $pos++;
  $offset += (4 - $pos);
  return "...." . trim($state, ".") . "....";
}

function calc_sum($offset, string $pots)
{
  $result = 0;
  $offset *= -1;

  for ($i = 0; $i < strlen($pots); $i++) {
    if ($pots[$i] == '#')
      $result += $offset;
    $offset++;
  }
  return $result;
}

function fast_forward($notes, $generations)
{
  $more_than_three = 3;
  $repeating_number = 0;
  $b = 0;
  $offset = 0;
  $state = $notes['state'];
  unset($notes['state']);

  while ($generations--) {
    $state = calibrate($state, $offset);
    $new_state = "..";

    for ($i = 2; $i < strlen($state); $i++) {
      $pattern = substr($state, ($i - 2), 5);
      $replace = '.';
      foreach ($notes as $note) {
        if ($note['pattern'] == $pattern) {
          $replace = $note['prod'];
          break;
        }
      }
      $new_state .= $replace;
    }

    $a = $b;
    $state = $new_state;
    $b = calc_sum($offset, $state);
    if ($repeating_number == $b - $a) {
      if (!$more_than_three--)
        return $repeating_number * $generations + $b;
    }
    else
      $more_than_three = 3;
    $repeating_number = $b - $a;
  }
  return calc_sum($offset, $state);
}

function digest_input($input)
{
  $regex = "/([.#]+) => ([.#])/";

  foreach ($input as $row) {
    if (!$row)
      continue;

    if (preg_match($regex, $row, $match))
      $ret[] = ['pattern' => $match[1], 'prod' => $match[2]];
    else {
      if (preg_match("/initial state: ([.#]+)/", $row, $match));
        $ret['state'] = $match[1];
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
    // Part 1
    echo "Part 1:\n";
    $start = microtime(true);
    $input = digest_input(explode("\n", $input));
    $result = fast_forward($input, 20);
    echo "The sum of numbers of all pots that contain a plant: \033[0;32m".$result."\033[0m\n";
    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    $result = fast_forward($input, 50000000000);
    echo "The sum of numbers of all pots that contain a plant after 50 Bilion generations: \033[0;32m".$result."\033[0m\n";
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
