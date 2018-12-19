<?PHP
ini_set('memory_limit', '1024M');

class Marble
{
  public $marble;
  public $next;
  public $prev;

  public function __construct($prev, $marble, $next) {
    $this->marble = $marble;
    $this->next = $next;
    $this->prev = $prev;
  }
}

class MarbleCircle
{
  // circle
  private $circle;

  public function __construct($first) {
    $this->circle = $first;
  }

  /*
   * move current position in circle
   */
  public function next() {
    $this->circle = $this->circle->next;
  }

  public function prev() {
    $this->circle = $this->circle->prev;
  }

  /*
   * Inserts a new node in list at next position.
   */
  public function insert($marble) {
    $save = $this->circle->next;
    $this->circle->next = new Marble($this->circle, $marble, $save);
    $this->circle = $this->circle->next;
    $this->circle->next->prev = $this->circle;
  }

  /*
   * deletes current node from list
   */
  public function delete() {
    $this->circle->prev->next = $this->circle->next;
    $this->circle->next->prev = $this->circle->prev;
    $del = $this->circle;
    $this->circle = $this->circle->next;
    $ret = $del->marble;
    $del->next = NULL;
    $del->prev = NULL;
    unset($del);
    return $ret;
  }
}

function marble_game($players, $marbles, $score)
{
  $points = array_fill(1, $players, 0);
  $player = 0;
  $current_marble = 0;

  $first = new Marble(NULL, 0, NULL);
  $first->prev = $first;
  $first->next = $first;

  $circle = new MarbleCircle($first);

  while (++$current_marble <= $marbles) {
    if (++$player > $players)
      $player = 1;

    if ($current_marble % 23 == 0) {
      $points[$player] += $current_marble;
      for ($i = 7; $i > 0; $i--)
        $circle->prev();
      $points[$player] += $circle->delete();
    }
    else
    {
      $circle->next();
      $circle->insert($current_marble);
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
    echo "Please be patient, the marbles are multiplied by 100!\n";
    $start = microtime(true);
    gc_disable();
    foreach ($input as $game) {
      marble_game($game['players'], ($game['marbles'] * 100), "?");
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
