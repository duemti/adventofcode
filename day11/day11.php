<?PHP

function power_level($grid, $x, $y, $dx, $dy)
{
  $power = 0;
  $maxy = $y + $dy;

  while ($y < $maxy) {
    $save = $grid[$y];
    $power += array_sum(array_values(array_slice($save, $x, $dx)));
    $y++;
  }
  return $power;
}

function create_grid($serial)
{
  for ($y = 0; $y < 300; $y++) {
    for ($x = 0; $x < 300; $x++) {
      $rackid = $x + 10;
      $powerl = ($rackid * $y + $serial) * $rackid;
      $pl = floor(($powerl % 1000) / 100) - 5;

      // inserting in the grid
      $grid[$y][$x] = $pl;
    }
  }
  return $grid;
}

function find_grid($grid, $dx, $dy)
{
  $maxy = 300 - $dy;
  $maxx = 300 - $dx;
  $cell = NULL;

  for ($y = 0; $y < $maxy; $y++) {
    for ($x = 0; $x < $maxx; $x++) {
      $level = power_level($grid, $x, $y, $dx, $dy);

      if (NULL == $cell || $cell['level'] < $level) {
        $cell['level'] = $level;
        $cell['x'] = $x;
        $cell['y'] = $y;
      }
    }
  }
  return $cell;
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
    echo "Part 1:\n";
    $start = microtime(true);
    $input = explode("\n", $input);
    foreach ($input as $serial_number) {
      if (!$serial_number)
        continue;
      $cell = find_grid(create_grid($serial_number), 3, 3);
      echo "Fuel cell at \033[0;32m".$cell['x']."\033[0m,\033[0;32m".$cell['y']."\033[0m; power level of grid \033[0;32m".$cell['level']."\033[0m\n";
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";
    echo "\nPart 2:\n";
    $start = microtime(true);
    foreach ($input as $serial_number) {
      if (!$serial_number)
        continue;
      $cell = NULL;
      $grid = create_grid($serial_number);
      echo "__________\n";
      for ($i = 1; $i <= 300; $i++) {
        if ($i % 30 == 0)
          echo "\033[0;33m#\033[0m";
        $new_cell = find_grid($grid, $i, $i);
        if ($cell == NULL || $new_cell['level'] > $cell['level']) {
          $cell = $new_cell;
          $cell['d'] = $i;
        }
      }
      echo "\nFuel cell at \033[0;32m".$cell['x']."\033[0m,\033[0;32m".$cell['y']."\033[0m with dimension of \033[0;32m".$cell['d']."\033[0m; power level of grid \033[0;32m".$cell['level']."\033[0m\n";
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
