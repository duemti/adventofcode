<?PHP
ini_set('memory_limit', '3048M');

class Recipe
{
  public $score;
  public $next;
  public $prev;

  public function __construct($score, $next = null, $prev = null) {
    $this->score = $score;
    $this->next = $next;
    $this->prev = $prev;
  }
}

class RecipesScores
{
  // Total number of recipes
  public $recipes = 0;

  public $sequence = "";

  // linked list
  private $start_recipe_score = null;
  private $end_recipe_score = null;

  // Elfs
  private $first_elf;
  private $second_elf;

  public function __construct($scores) {
    $this->append($scores[0]);
    $this->first_elf = $this->start_recipe_score;
    $this->append($scores[1]);
    $this->second_elf = $this->start_recipe_score->next;
  }

  /*
   * move to next score in list
   */
  public function move_elves() {
    $this->move_forward($this->first_elf, 1 + $this->first_elf->score);
    $this->move_forward($this->second_elf, 1 + $this->second_elf->score);
  }

  /*
   * move to previous score in list
   */
  public function move_forward(&$elf, $times) {
    while ($times--)
      $this->next($elf);
  }

  /**
   * move a elf to next position
   */
  private function next(&$elf)
  {
    if ($elf->next == null)
      $elf = $this->start_recipe_score;
    else
      $elf = $elf->next;
  }

  public function create_new_recipes()
  {
    $score_sum = $this->first_elf->score + $this->second_elf->score;

    if ($score_sum > 9) {
      $this->append((int)($score_sum / 10));
      $this->append($score_sum % 10);
    }
    else
      $this->append($score_sum);
  }

  /**
   * Append at the end of the list.
   */
  private function append($score) {
    if ($this->end_recipe_score == null) {
      $this->start_recipe_score = new Recipe($score);
      $this->end_recipe_score = $this->start_recipe_score;
    }
    else {
      $this->end_recipe_score->next = new Recipe($score, null, $this->end_recipe_score);
      $this->end_recipe_score = $this->end_recipe_score->next;
    }

    $this->recipes++;
    $this->sequence .= $score;
  }

  public function last(int $count, int $after)
  {
    $scores = "";
    $end = $this->end_recipe_score;
    if ($count + $after < $this->recipes)
      $end = $end->prev;

    while ($count-- && $end != null) {
      $scores = $end->score . $scores;
      $end = $end->prev;
    }
    return $scores;
  }

  /**
   * outputs the scoreboard
   */
  public function output()
  {
    $rs = $this->start_recipe_score;

    while ($rs != null) {
      $s = ($rs == $this->first_elf) ? "(": (($rs == $this->second_elf) ? "[" : " ");
      $e = ($rs == $this->first_elf) ? ")": (($rs == $this->second_elf) ? "]" : " ");

      echo $s . $rs->score . $e;
      $rs = $rs->next;
    }
    echo PHP_EOL;
  }
}

/**
 * @param int $scores - number of scores to return
 * @param int $after - number of scores after which to return desired scores
 * @param int $first - first recipe score
 * @param int $second - second recipe score
 *
 * @return string - scores
 */
function find_score(int $scores, int $after, int $first, int $second)
{
  $scoreboard = new RecipesScores([$first, $second]);

//  $scoreboard->output();
  while ($scoreboard->recipes < $after + $scores) {
    $scoreboard->create_new_recipes();
    $scoreboard->move_elves();
//    $scoreboard->output();
  }
  return $scoreboard->last($scores, $after);
}

function find_amount_scores(string $sequence, int $first, int $second)
{
  $pos = 0;
  $len = strlen($sequence);
  $scoreboard = new RecipesScores([$first, $second]);

  while (FALSE === ($pos = strpos($scoreboard->sequence, $sequence))) {
    if (strlen($scoreboard->sequence) > $len)
      $scoreboard->sequence = substr($scoreboard->sequence, ($len + 1) * -1);
    $scoreboard->create_new_recipes();
    $scoreboard->move_elves();
  }
  return $scoreboard->recipes - strlen($scoreboard->sequence) + $pos;
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
    $input = explode("\n", $input);
    foreach ($input as $scores) {
      if (!$scores)
        continue;
      $result = find_score(10, (int)$scores, 3, 7);
      echo "Scores after provided input number of scores: \033[0;32m".$result."\033[0m\n";
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";

    // Part 2
    echo "\nPart 2:\n";
    $start = microtime(true);
    gc_disable();
    foreach ($input as $sequence) {
      if (!$sequence)
        continue;

      $result = find_amount_scores($sequence, 3, 7);
      echo "Number of scores to the left of the sequence '".$sequence."' is \033[0;32m".$result."\033[0m\n";
    }
    echo "Done in ".(microtime(true) - $start)." sec.\n";
  }
}
