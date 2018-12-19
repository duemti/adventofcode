<?PHP

function check_step($step, $steps)
{
  foreach ($steps as $before) {
    if (in_array($step, $before))
      return FALSE;
  }
  return TRUE;
}

function find_order($steps)
{
  $order = "";

  echo "Arranging steps...\n";
  while (count($steps)) {
    $to_remove = [];
    foreach ($steps as $step => $before) {
      if (check_step($step, $steps)) {
        $to_remove[] = $step;
      }
    }
    asort($to_remove);
    $order .= current($to_remove);
    unset($steps[current($to_remove)]);
  }
  return $order;
}

function arrange_steps($input)
{
  $steps = [];
  $regex = "/Step ([A-Z]) must be finished before step ([A-Z]) can begin./";

  echo "Reading input...\n";
  foreach ($input as $row) {
    if (!$row)
      continue;
    preg_match($regex, $row, $result);

    $steps[$result[1]][] = $result[2];
    if (!array_key_exists($result[2], $steps))
      $steps[$result[2]] = array();
  }
  return $steps;
}

function assign_work(&$workers, $work)
{
  foreach ($workers as &$worker) {
    if (empty($worker)) {
      $worker = $work;
      return true;
    }
  }
  return false;
}

function start_work(&$workers, &$steps, &$steps_done)
{
  foreach ($workers as &$worker) {
    if (empty($worker))
      continue;

    if (0 == --$worker['time']) {
      $steps_done .= $worker['step'];
      unset($steps[$worker['step']]);
      $worker = [];
    }
  }
}

/**
 * 5 Workers and 60 seconds a step
 */
function execute_steps($ordered_steps, $steps)
{
  $time = 0;
  $steps_done = "";
  $workers = array([], [], [], [], []);
  $o_steps = str_split($ordered_steps);

  echo "Executing the steps...\n";
  while ($steps) {

    # Can't proceed to next step.
    foreach ($o_steps as &$s)
    {
      if (!$s)
        continue;

      if (check_step($s, $steps)) {
        if (assign_work($workers, ['step' => $s, 'time' => ord($s) - 4])) {
          $s = '';
        }
      }
    }

    // One Second.
    start_work($workers, $steps, $steps_done);
    $time++;
  }
  return $steps_done . " in \033[0;32m". $time . "\033[0m seconds.";
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
    $steps = arrange_steps(explode("\n", $input));
    $steps_order = find_order($steps);
    echo "Part 1: The order is: \033[0;32m" .$steps_order. "\033[0m\n";
    echo "Part 2: Steps were executed in following order: ".execute_steps($steps_order, $steps) ."\n";
  }
}
