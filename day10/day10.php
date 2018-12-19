<?PHP
ini_set('memory_limit', '2024M');

function move(&$position, $velocity)
{
  for ($i = 0; $i < count($position); $i++) {
    $position[$i]['py'] += $velocity[$i]['vy'];
    $position[$i]['px'] += $velocity[$i]['vx'];
  }
}

function find_message($position, $velocity)
{
  $size = find_size_grid($position);
  $new_position = $position;
  $seconds = 0;

  while (1) {
    move($new_position, $velocity);
    $new_size = find_size_grid($new_position);

    if ($size['x'] > $new_size['x'] &&
      $size['X'] < $new_size['X'] &&
      $size['y'] > $new_size['y'] &&
      $size['Y'] < $new_size['Y']) {
      break ;
    }
    $size = $new_size;
    $position = $new_position;
    $seconds++;
  }
  print_grid($position, $size);
  return $seconds;
}

function print_grid($position, $size)
{
  for ($y = $size['y']; $y <= $size['Y']; $y++) {
    for ($x = $size['x']; $x <= $size['X']; $x++) {
      if (in_array(array('py' => $y, 'px' => $x), $position))
        echo "#";
      else
        echo ".";
    }
    echo "\n";
  }
}

function find_size_grid($position)
{
  foreach ($position as $pos) {
    $y[] = $pos['py'];
    $x[] = $pos['px'];
  }
  $size['x'] = min($x);
  $size['X'] = max($x);
  $size['y'] = min($y);
  $size['Y'] = max($y);

  return $size;
}

function digest_input($input, &$position, &$velocity)
{
  $ret = [];
  $regex = "/position=< *(-?\d+), *(-?\d+)> velocity=< *(-?\d+), *(-?\d+)>/";

  foreach ($input as $row) {
    if (preg_match($regex, $row, $match)) {
      $position[] = array('px' => (int)$match[1], 'py' => (int)$match[2]);
      $velocity[] = array('vx' => (int)$match[3], 'vy' => (int)$match[4]);
    }
  }
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
    $position = [];
    $velocity = [];
    digest_input(explode("\n", $input), $position, $velocity);
    echo "Part 1:\n";
    $start = microtime(true);
    $seconds = find_message($position, $velocity);
    echo "Done in ".(microtime(true) - $start)." sec.\n";
    echo "\nPart 2:\n";
    $start = microtime(true);
    echo "It would've took ".$seconds." seconds to wait for message to appear.\n";
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
