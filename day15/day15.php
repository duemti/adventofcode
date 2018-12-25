<?PHP

function exist_in_array($x, $y, $arr)
{
  foreach ($arr as $a) {
    if ($a['x'] == $x && $a['y'] == $y)
      return $a;
  }
  return false;
}

function output($cavern, $points = [], $mark = '?')
{
  foreach ($cavern as $y => $row) {
    foreach ($row as $x => $square) {
      echo (FALSE != exist_in_array($x, $y, $points)) ? "\033[0;32m".$mark."\033[0m" : $square;
    }
    echo PHP_EOL;
  }
}

function reading_order($squares, $cavern)
{
  $order = [];

  for ($y = 1; $y < count($cavern); $y++) {
    for ($x = 1; $x < count($cavern[$y]); $x++) {
      $temp = exist_in_array($x, $y, $squares);

      if ($temp !== FALSE)
        $order[] = $temp;
    }
  }
  return $order;
}

function detect_units_positions($cavern)
{
  $units = [];

  foreach ($cavern as $y => $row) {
    foreach ($row as $x => $square) {
      if ($square == 'G' || $square == 'E')
        $units[] = ['unit' => $square, 'x' => $x, 'y' => $y, 'enemy' => (($square == 'G') ? 'E' : 'G'), 'hp' => 200];
    }
  }
  return $units;
}

function find_adjacent_squares($x, $y, $cavern, $type)
{
  $open_squares = [];

  if (isset($cavern[$y - 1][$x]) && $cavern[$y - 1][$x] == $type)
    $open_squares[] = ['x' => $x, 'y' => $y - 1];
  if (isset($cavern[$y][$x - 1]) && $cavern[$y][$x - 1] == $type)
    $open_squares[] = ['x' => $x - 1, 'y' => $y];
  if (isset($cavern[$y][$x + 1]) && $cavern[$y][$x + 1] == $type)
    $open_squares[] = ['x' => $x + 1, 'y' => $y];
  if (isset($cavern[$y + 1][$x]) && $cavern[$y + 1][$x] == $type)
    $open_squares[] = ['x' => $x, 'y' => $y + 1];
  return $open_squares;
}

function in_range_squares($current_unit, $all_units, $cavern)
{
  $squares = [];


  foreach ($all_units as $unit) {
    if ($unit != $current_unit && $unit['unit'] != $current_unit['unit']) {
      $squares = array_merge($squares, find_adjacent_squares($unit['x'], $unit['y'], $cavern, "."));
    }
  }

  return $squares;
}

function detect_reachable_squares($unit, $destinations, $cavern)
{
  $open_reachable_squares = [];
  $current[] = ['x' => $unit['x'], 'y' => $unit['y']];
  $distance = 0;

  while (!empty($current)) {
    $new_dest = [];

    foreach ($current as $square) {
      if ( in_array($square, $destinations) )
        $open_reachable_squares[] = $square + ['distance' => $distance];

      // find the path
      $new_dest = array_merge($new_dest, find_adjacent_squares($square['x'], $square['y'], $cavern, "."));

      // Mark already read squares.
      $cavern[ $square['y'] ][ $square['x'] ] = "x";
    }
    $current = array_unique($new_dest, SORT_REGULAR);
    $distance++;
  }
  return $open_reachable_squares;
}

function find_closest_target($squares)
{
  $closest = [];

  foreach ($squares as $square) {
    if ($closest == [] || $square['distance'] < $closest['distance'])
      $closest = $square;
  }
  return $closest;
}

function find_shortest_path($x, $y, $unit, $cavern)
{
  $distance = 0;
  $dist = [];
  $dest = [['x' => $x, 'y' => $y]];
  $cavern[$y][$x] = 0;

  while (!empty($dest)) {
    $distance++;
    $new = [];

    foreach ($dest as $d)  {
      $new = array_merge($new, find_adjacent_squares($d['x'], $d['y'], $cavern, "."));
      $dist[] = array_merge($d, ['distance' => $distance]);
      $cavern[$d['y']][$d['x']] = "x";
    }
    $dest = array_unique($new, SORT_REGULAR);
    if (exist_in_array($unit['x'], $unit['y'], $dest))
      break;
  }

  $distance = -1;
  foreach ([
    ['y' => $unit['y'] - 1, 'x' => $unit['x']],
    ['y' => $unit['y'], 'x' => $unit['x'] - 1],
    ['y' => $unit['y'], 'x' => $unit['x'] + 1],
    ['y' => $unit['y'] + 1, 'x' => $unit['x']]
  ] as $c) {
    if (false === ($d = exist_in_array($c['x'], $c['y'], $dist)))
      continue;

    if (!isset($path) || $d['distance'] < $path['distance']) {
      $path = $d;
    }
  }
  return $path;
}

function attack($curr, $len, &$units, &$cavern, $elf_attack, &$full_rounds)
{
  $unit = $units[$curr];

  foreach ([
    ['y' => $unit['y'] - 1, 'x' => $unit['x']],
    ['y' => $unit['y'], 'x' => $unit['x'] - 1],
    ['y' => $unit['y'], 'x' => $unit['x'] + 1],
    ['y' => $unit['y'] + 1, 'x' => $unit['x']]
  ] as $adj) {
    if ($cavern[$adj['y']][$adj['x']] == $unit['enemy'])
      $targets[] = exist_in_array($adj['x'], $adj['y'], $units);
  }

  if (!isset($targets))
    return FALSE;

  // ATTACK !!!
  foreach ($targets as $t) {
    if (!isset($target) || $t['hp'] < $target['hp'])
      $target = $t;
  }

  foreach ($units as $i => &$u) {
    if ($target == $u) {

      $u['hp'] -= ($unit['unit'] == 'G') ? 3 : $elf_attack;
      if ($u['hp'] <= 0) {
        $cavern[$u['y']][$u['x']] = '.';
        unset($units[$i]);
        while (!isset($units[++$curr]) && $curr < $len)
          ;
        if (check_units($units) == false && $curr < $len)
          $full_rounds--;
      }
      return true;
    }
  }
  return false;
}

function check_units($units)
{
  $goblins = false;
  $elves = false;

  foreach ($units as $unit) {
    if ($unit['unit'] == 'E')
      $elves = true;
    else if ($unit['unit'] == 'G')
      $goblins = true;
  }
  return ($elves && $goblins) ? true : false;
}

function hp_sum($units)
{
  $sum = 0;

  foreach ($units as $unit) {
    $sum += $unit['hp'];
  }
  return $sum;
}

function start_game($cavern, $elf_attack = 3)
{
  $full_rounds = 0;

  // Maps all units positions. Also in the order in which they need to take turns.
  $units = detect_units_positions($cavern);
  while (check_units($units)) {
    $units = reading_order($units, $cavern);
    $len = count($units);

    // Each unit is taking their turn.
    for ($i = 0; $i < $len; $i++) {
      if (!isset($units[$i]))
        continue;
      $unit = $units[$i];

      // Check if it is already near target
      if (attack($i, $len, $units, $cavern, $elf_attack, $full_rounds) === true)
        continue;

      // identifies all adjacent open squares ('.') of a target.
      $open_squares = in_range_squares($unit, $units, $cavern);
      if (empty($open_squares))
        continue;

      // determines which open squares can be reached.
      $open_squares = detect_reachable_squares($unit, $open_squares, $cavern);
      if (empty($open_squares))
        continue;

      // find closest square
      $square = find_closest_target(reading_order($open_squares, $cavern));

      $square = find_shortest_path($square['x'], $square['y'], $unit, $cavern);

      // Move unit.
      $cavern[ $unit['y'] ][ $unit['x'] ] = '.';
      $cavern[ $square['y'] ][ $square['x'] ] = $unit['unit'];
      $units[$i]['x'] = $square['x'];
      $units[$i]['y'] = $square['y'];

      // After moving unit can still attack.
      attack($i, $len, $units, $cavern, $elf_attack, $full_rounds);
    }
    $full_rounds++;
  }
  return ['units_alive' => count($units), 'rounds' => $full_rounds, 'win' => ((current($units)['unit'] == 'E') ? "Elves" : "Goblins" ),'total_hp' => hp_sum($units)];
}

function digest_input($input)
{
  $cave = [];
  $regex = "/Outcome: (\d*) \* (\d*) = (\d*)/";

  $pipe = &$cave['cave'];
  foreach ($input as $line) {
    if (!$line)
      continue;

    if ($line == "Expected") {
      $pipe = &$cave['expected'];
      continue;
    }

    if (preg_match($regex, $line, $match)) {
      $cave['result'] = ['rounds' => (int)$match[1], 'total_hp' => (int)$match[2], 'total' => (int)$match[3]];
      break;
    }

    $pipe[] = str_split($line);
  }
  return $cave;
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
    $input = digest_input(explode("\n", $input));
    // Part 1
    echo "Part 1:\n";

    $start = microtime(true);
    $result = start_game($input['cave']);
    echo "Combat end after \033[0;33m".$result['rounds']."\033[0m full rounds.\n";
    echo $result['win']." win with \033[0;33m".$result['total_hp']."\033[0m total hitpoints left.\n";
    echo "Outcome; \033[0;35m".$result['rounds']."\033[0m * \033[0;35m".$result['total_hp']."\033[0m = \033[0;32m".($result['rounds'] * $result['total_hp'])."\033[0m\n";
    if (isset($input['result'])) {
      echo (($input['result']['rounds'] == $result['rounds']) ? "\033[0;32mRounds: \033[0m" : "\033[0;31mRounds:\033[0m ").$input['result']['rounds']." <=> ".$result['rounds'].PHP_EOL;
      echo (($input['result']['total_hp'] == $result['total_hp']) ? "\033[0;32mTotal HP: \033[0m" : "\033[0;31mTotal HP:\033[0m ").$input['result']['total_hp']." <=> ".$result['total_hp'].PHP_EOL;
      echo (($input['result']['total'] == $result['rounds']*$result['total_hp']) ? "\033[0;32mMatched expected: \033[0m" : "\033[0;31mDid not matched: \033[0m").$input['result']['total'].PHP_EOL;
    }

    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    $total_elves = 0;
    $elf_attack = 3;
    unset($result);

    foreach (detect_units_positions($input['cave']) as $unit) {
      if ($unit['unit'] == 'E')
        $total_elves++;
    }

    while (!isset($result) || $result['win'] != "Elves" || $result['units_alive'] != $total_elves)
      $result = start_game($input['cave'], $elf_attack++);
    echo "Combat end after \033[0;33m".$result['rounds']."\033[0m full rounds.\n";
    echo $result['win']." win with \033[0;33m".$result['total_hp']."\033[0m total hitpoints left.\n";
    echo "Elves attack power was: \033[0;32m".--$elf_attack."\033[0m\n";
    echo "Outcome; \033[0;35m".$result['rounds']."\033[0m * \033[0;35m".$result['total_hp']."\033[0m = \033[0;32m".($result['rounds'] * $result['total_hp'])."\033[0m\n";

    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
