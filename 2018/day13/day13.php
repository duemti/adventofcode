<?PHP

function cart_at($x, $y, $carts)
{
  foreach ($carts as $cart) {
    if ($cart['x'] == $x && $cart['y'] == $y) {
      return $cart;
    }
  }
  return false;
}

function print_system($system, $carts, $col)
{
  for ($y = 0; $y < count($system); $y++) {
    for ($x = 0; $x < count($system[$y]); $x++) {
      $track = $system[$y][$x];

      if ($c = cart_at($x, $y, $carts)) {
        echo "\033[0;32m";
        $track = $c['direction'];
      }
      else if (in_array([$x, $y], $col)) {
        echo "\033[0;31m";
        $track = "X";
      }

      echo $track."\033[0m";
    }
    echo PHP_EOL;
  }
}

function check_for_collision(&$carts)
{
  $col = null;

  foreach ($carts as $pos_1 => $cart) {

    // Continue from previous pointer.
    $carts_copy = $carts;
    foreach ($carts_copy as $pos_2 => $next_cart) {
      // If it is the same cart
      if ($pos_1 == $pos_2)
        break;

      if ($cart['x'] == $next_cart['x'] && $cart['y'] == $next_cart['y']) {
        $col[] = [$cart['x'], $cart['y']];
        unset($carts[$pos_1]);
        unset($carts[$pos_2]);
        break;
      }
    }
  }
  return $col;
}

function find_track_form($x, $y, $system)
{
  $up = (isset($system[$y - 1][$x]) && in_array($system[$y - 1][$x],    ['v', '^', '<', '>', '+', '|', '\\', '/'])) ? true : false;
  $down = (isset($system[$y + 1][$x]) && in_array($system[$y + 1][$x],  ['v', '^', '<', '>', '+', '|', '\\', '/'])) ? true : false;
  $left = (isset($system[$y][$x - 1]) && in_array($system[$y][$x - 1],  ['v', '^', '<', '>', '+', '-', '\\', '/'])) ? true : false;
  $right = (isset($system[$y][$x + 1]) && in_array($system[$y][$x + 1], ['v', '^', '<', '>', '+', '-', '\\', '/'])) ? true : false;


  if ($up && $down && $right && $left)
    return '+';
  if ($up && $down)
    return '|';
  if ($left && $right)
    return '-';
  if (($up && $left) || ($down && $right))
    return '/';
  if (($up && $right) || ($down && $left))
    return '\\';
  return '?';
}

function update_intersection_choice($dir, &$choice)
{
  if ($choice == 'left') {
    $choice = "straight";
    return ($dir == "^") ? "<" : (($dir == ">") ? "^" : (($dir == "v") ? ">" : "v"));
  }
  if ($choice == "straight") {
    $choice = "right";
    return $dir;
  }
  if ($choice == "right") {
    $choice = "left";
    return ($dir == "^") ? ">" : (($dir == ">") ? "v" : (($dir == "v") ? "<" : "^"));
  }
}

function reorder_carts($carts, $system)
{
  $carts_order = [];

  for ($y = 0; $y < count($system); $y++) {
    for ($x = 0; $x < count($system[$y]); $x++) {
      $temp = $carts;

      foreach ($temp as $cart) {
        if ($y == $cart['y'] && $x == $cart['x']) {
          $cart['was_moved'] = false;
          $carts_order[] = $cart;
        }
      }
    }
  }
  return $carts_order;
}

function tick(&$carts, $system, &$collisions)
{
  $len = count($carts);

  for ($i = 0; $i < $len; $i++) {
    if (!isset($carts[$i]))
      continue;
    $cart = &$carts[$i];
    $new_direction = 'x';
    $track = "?";

    switch ($cart['direction']) {

      case '<':
        $track = $system[ $cart['y'] ][ --$cart['x'] ];

        switch ($track) {
          case '+':
            $new_direction = update_intersection_choice("<", $cart['intersection']);
            break;
          case '\\':
            $new_direction = '^';
            break;
          case '/':
            $new_direction = 'v';
            break;
          case '-':
            $new_direction = '<';
            break;
        }
        break;

      case '>':
        $track = $system[ $cart['y'] ][ ++$cart['x'] ];

        switch ($track) {
          case '+':
            $new_direction = update_intersection_choice(">", $cart['intersection']);
            break;
          case '\\':
            $new_direction = 'v';
            break;
          case '/':
            $new_direction = '^';
            break;
          case '-':
            $new_direction = '>';
        }
        break;

      case '^':
        $track = $system[ --$cart['y'] ][ $cart['x'] ];

        switch ($track) {
          case '+':
            $new_direction = update_intersection_choice("^", $cart['intersection']);
            break;
          case '\\':
            $new_direction = '<';
            break;
          case '/':
            $new_direction = '>';
            break;
          case '|':
            $new_direction = '^';
        }
        break;

      case 'v':
        $track = $system[ ++$cart['y'] ][ $cart['x'] ];

        switch ($track) {
          case '+':
            $new_direction = update_intersection_choice("v", $cart['intersection']);
            break;
          case '\\':
            $new_direction = '>';
            break;
          case '/':
            $new_direction = '<';
            break;
          case '|':
            $new_direction = 'v';
        }
        break;
    }

    $cart['direction'] = $new_direction;
    $cart['track'] = $track;
    $cart['was_moved'] = true;

    if ($col = check_for_collision($carts)) {
      $collisions = array_merge($collisions, $col);
      echo "A collision detected. Total: \033[0;33m".count($collisions)." collisions. \033[0;32m".count($carts)."\033[0m carts remaining.\n";
    }
  }
}

function find_collisions($carts, $system, $print = false)
{
  $col = [];
  $ticks = 0;

  while (count($carts) > 1) {
    $carts = reorder_carts($carts, $system);

    // Perform a tick move. This means to move all carts.
    tick($carts, $system, $col);

    if ($print)
      print_system($system, $carts, $col);
    $ticks++;
  }

  print_system($system, $carts, $col);
  foreach ($col as $c) {
    echo "Collision occured at \033[0;32m".$c[0]."\033[0m,\033[0;32m".$c[1]."\033[0m, at ".$ticks." tick.\n";
  }

  echo "\033[0;32m".(($carts) ? count($carts) : "0")."\033[0m cart(s) remaining".PHP_EOL;
  foreach ($carts as $cart) {
    echo "Cart at \033[0;32m".$cart['x']."\033[0m,\033[0;32m".$cart['y']."\033[0m\n";
  }
}

function remove_carts($carts, &$system)
{
  for ($y = 0; $y < count($system); $y++) {
    for ($x = 0; $x < count($system[$y]); $x++) {
      foreach ($carts as $cart) {
        if ($cart['x'] == $x && $cart['y'] == $y)
          $system[$y][$x] = find_track_form($cart['x'], $cart['y'], $system);
      }
    }
  }
}

function digest_input(&$input)
{
  $carts = [];
  $system = [];

  for ($y = 0; $y < count($input); $y++) {
    $row = $input[$y];
    $tracks = [];

    for ($x = 0; $x < strlen($row); $x++) {
      if (in_array($row[$x], ['<', '>', 'v', '^'])) {
        $carts[] = ['direction' => $row[$x], 'x' => $x, 'y' => $y, 'track' => null, 'intersection' => 'left', 'was_moved' => false];
      }
      $tracks[] = $row[$x];
    }
    $system[] = $tracks;
  }
  $input = $system;
  return $carts;
}

if ($argc < 2 && $argc > 3) {
  echo "Usage: ".$argv[0]." [input file] [print]\n\tprint - to print tracks every tick.";
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
    $input = explode("\n", $input);
    $carts = digest_input($input);
    remove_carts($carts, $input);

    find_collisions($carts, $input, ((isset($argv[2]) && $argv[2] == "print") ? true : false));
    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
