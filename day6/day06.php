<?php

function find_closest_pin($point, $coor, $mx, $my)
{
    $mark = '';
    $distance = -1;
    $y = -1;

    foreach ($coor as $m => $c) {
        $a = abs($point['x'] - $c[0]);
        $b = abs($point['y'] - $c[1]);

        $d = $a + $b;
        if ($d == 0)
            return $m;

        if (0 > $distance || $d < $distance)
        {
            $mark = strtolower($m);
            $distance = $d;
        }
        else if ($distance == $d) {
            $mark = '.';
        }
    }
    return $mark;
}

function find_biggest_area($map, $mx, $my, $coordinates)
{
    $not_qualified = [];
    $count = [];

    echo "Counting areas...\n";
    for ($y = 0; $y < $my; $y++)
    {
        for ($x = 0; $x < $mx + 1; $x++)
        {
          $c = strtoupper($map[$y][$x]);

            if (!array_key_exists($c, $count))
                $count[$c] = 0;
            $count[$c] += 1;

            if ($x == 0 || $y == 0 || $x == $mx || $y == $my)
            {
                if (!in_array($c, $not_qualified))
                    $not_qualified[] = $c;
            }
        }
    }


    foreach ($not_qualified as $coor) {
        if (array_key_exists($coor, $count))
          unset($count[$coor]);
    }
    $largest = 0;
    foreach ($count as $area) {
      if ($area > $largest)
         $largest = $area;
    }
    return $largest;
}

function location_match($point, $coor, $match)
{
    $distance = 0;

    foreach ($coor as $m => $c) {
        $a = abs($point['x'] - $c[0]);
        $b = abs($point['y'] - $c[1]);

        $d = $a + $b;

        $distance += $d;
    }
    return ($distance < $match) ? true : false;
}

function calculate_coordinates($input)
{
    $coor = explode("\n", $input);
    $max_x = 0;
    $max_y = 0;
    $pin = 'A';

    foreach ($coor as $c) {
        if ($c)
        {
            $temp = explode(", ", $c);
            $coordinates[$pin++] = $temp;
            if ($temp[0] > $max_x)
                $max_x = $temp[0];
            if ($temp[1] > $max_y)
                $max_y = $temp[1];
        }
    }

    for ($x = 0; $x <= $max_x + 1; $x++) {
        for ($y = 0; $y <= $max_y; $y++) {
            $map[$y][$x] = find_closest_pin(['x' => $x, 'y' => $y], $coordinates, $max_x, $max_y);
        }
    }

    $area = find_biggest_area($map, $max_x, $max_y, $coordinates);

    echo "PART 1: The largest area that isn't infinite is ". $area ."\n";

    $match = 0;
    echo "\nFinding matching locations...\n";
    for ($x = 0; $x <= $max_x + 1; $x++) {
        for ($y = 0; $y <= $max_y; $y++) {
          if (location_match(['x' => $x, 'y' => $y], $coordinates, 10000))
            $match++;
        }
    }
    echo "PART 2: The number of locations that fit under 10.000 are: ".$match."\n";
}

$input = file_get_contents("input2.txt");
if (!$input)
{
    echo "Failed to open 'input2.txt'\n";
}
else
    calculate_coordinates($input);
