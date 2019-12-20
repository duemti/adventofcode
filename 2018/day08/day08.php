<?PHP

function calculate_metadata(&$tree, $input, $size, &$i = 0, $pos = 0)
{
  if ($i >= $size)
    return 0;

  $sum = 0;
  $child = (int)$input[$i++];
  $tree[$pos]['childs'] = $child;
  $metadata = (int)$input[$i++];

  for ($c = 0; $c < $child; $c++)
    $sum += calculate_metadata($tree[$pos], $input, $size, $i, $c);

  while ($metadata-- > 0) {
    $sum += (int)$input[$i];
    $tree[$pos]['metadata'][] = (int)$input[$i];
    $i++;
  }

  return $sum;
}

function cal_root_value($node)
{
  $sum = 0;

  if ($node['childs'] > 0) {
    foreach ($node['metadata'] as $md) {
      $sum += (array_key_exists(--$md, $node)) ? cal_root_value($node[$md]) : 0;
    }
  }
  else
    $sum += array_sum($node['metadata']);
  return $sum;
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
    echo "Part 1.\n";
    $input = explode(" ", $input);
    echo "Calculating the metadata...\n";
    $tree = [];
    $result = calculate_metadata($tree, $input, count($input));
    echo "The sum of metadata is \033[0;32m".$result."\033[0m.\n";
    echo "\nPart 2.\n";
    echo "Calculating root value...\n";
    $result = cal_root_value($tree[0]);
    echo "The value of root node is \033[0;32m".$result."\033[0m.\n";
  }
}
