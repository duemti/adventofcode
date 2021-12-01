<?PHP

$filename = isset($argv[1]) ? $argv[1] : './input.txt';
if (FALSE === file_exists($filename))
	die("Error: Non-existent file '$filename'.\n");

// line by line into an array
$input = file_get_contents($filename);

// parse the input
$input = parse($input);
$corners = part_one($input);
$roughness = part_two($input, $corners);

echo "Part 1: \e[32m", array_product(array_keys($corners)), "\e[0m\n";
echo "Part 2: The water roughness is: \e[32m", $roughness, "\e[0m\n";


// Data input: array of tileswith margins bitified.
function	part_one(array $tiles): array
{
	$matches = [];
	$corners = [];
	$answer = 1;

	// 1. Find all neighbours for all tiles.
	foreach ($tiles as $tile)
	{
		$matches[ $tile['id'] ]['normal'] = [];
		$matches[ $tile['id'] ]['flipped'] = [];

		foreach (array_keys($tiles) as $key)
		{
			if ($tiles[ $key ]['id'] === $tile['id'])
				continue ;

			foreach (compose($tiles[ $key ]) as $state => $nt)
			{
				foreach (['n' => 's', 'e' => 'w', 's' => 'n', 'w' => 'e'] as $s1 => $s2)
				{
					if (($res = array_keys($nt[ $s1 ], $tile[ $s2 ], true)))
					{
						$matches
							[ $tile['id'] ]
							[ $state ]
							[ $s2 ] = $tiles[ $key ]['id'];
					}
				}
			}
		}
		
		// 2. Find Corner tiles
		if (count($matches[ $tile['id'] ]['normal']) === 2 && count($matches[ $tile['id'] ]['flipped']) === 0)
			$corners[ $tile['id'] ] = array_keys($matches[ $tile['id'] ]['normal']);
		elseif (count($matches[ $tile['id'] ]['flipped']) === 2 && count($matches[ $tile['id'] ]['normal']) === 0)
			$corners[ $tile['id'] ] = array_keys($matches[ $tile['id'] ]['flipped']);
	}
	return $corners;
}

function	part_two(array $tiles, array $corners): int
{
	// 1. pick a corner
	$corn = pick_corner($tiles, $corners);

	// 2. Assemble image
	$matrix = assemble($tiles, $corn['id'], $corn['tile']);

	foreach ($matrix as $row)
		echo implode(' ', array_column($row, 'id')), PHP_EOL;

	// 3. Form the image
	$image = form_image($matrix);
	echo PHP_EOL;

	// 4. Search for Sea Monsters
	foreach ([$image, array_map("array_reverse", $image)] as $img)
	{
		for ($i = 0; $i < 4; $i++)
		{
			if (($mf = search_sea_monsters($img)))
			{
				$image = $img;
				break 2;
			}
			$img = rotate($img);
		}
	}

	// 5. Print the result for visualization purposes.
	echo "FOUND \e[32m$mf\e[0m Sea Monsters!\n";
	foreach ($image as $row)
		echo implode($row), PHP_EOL;

	// 6. Determine the water roughness.
	$roughness = 0;

	foreach ($image as $row)
		$roughness += count(array_keys($row, "#"));
	return $roughness;
}

function	search_sea_monsters(array &$image)
{
	$monsters_found = 0;

	foreach ($image as $y => $row)
	{
		foreach ($row as $x => $pixel)
		{
			// Scan for the monster
			if (false === scan_for_monster($image, $y, $x))
				continue ;

			// Arrive here ONLY if scan found a sea monster.
			$monsters_found++;
			scan_for_monster($image, $y, $x, true);
		}
	}
	return $monsters_found;
}

function	scan_for_monster(array &$image, int $y, int $x, bool $mark = false): bool
{
	// Coordinates that constitute a Sea Monster (y, x).
	$monster = [
		[0, 0], [1, 1], [1, 4], [0, 5], [0, 6], [1, 7],
		[1, 10], [0, 11], [0, 12], [1, 13], [1, 16],
		[0, 17], [0, 18], [-1, 18], [0, 19]
	];

	foreach ($monster as $mon)
	{
		if (!isset($image[ $y + $mon[0] ][ $x + $mon[1] ])
			|| $image[ $y + $mon[0] ][ $x + $mon[1] ] !== "#")
			return false;

		if ($mark)
			$image[ $y + $mon[0] ][ $x + $mon[1] ] = "\e[38;5;33mO\e[0m";
	}
	return true;
}

function	form_image(array $matrix): array
{
	$image = [];
	$y = 0;

	foreach ($matrix as $column)
	{
		foreach ($column as $row)
		{
			foreach ($row['tile']['core'] as $i => $core_row)
			{
				if (!isset($image[$y + $i]))
					$image[$y + $i] = [];

				$image[$y + $i] = array_merge($image[$y + $i], $core_row);
			}
		}
		$y += 8;
	}
	return $image;
}

function	assemble(array $tiles, int $id, array $cTile, string $horizontal = 'e', $vertical = 's'): array
{
	$y = 0;
	$x = 0;
	$matrix[$y][$x++] = ['id' => $id, 'tile' => $cTile];

	while (!empty($tiles))
	{
		foreach (array_keys($tiles) as $key)
		{
			$nextTile = $tiles[$key];
			$tileStates = [$nextTile, flip($nextTile)];
			$matched = false;

			// check the placement with upper tile
			if (!isset($matrix[$y - 1][$x]) && !isset($matrix[$y][$x - 1]))
				die("ERROR: NO NEIGHBOURS y=$y x=$x\n");
			// to east
			elseif (!isset($matrix[$y - 1][$x]))
			{
				foreach ($tileStates as $nt)
					if (($matched = cmp_tiles($matrix[$y][$x - 1]['tile'], $horizontal, $nt)))
						break ;
			}
			// to north
			elseif (!isset($matrix[$y][$x - 1]))
			{
				foreach ($tileStates as $nt)
					if (($matched = cmp_tiles($matrix[$y - 1][$x]['tile'], $vertical, $nt)))
						break ;
			} else {
				foreach ($tileStates as $nt)
					if (($matched = cmp_tiles($matrix[$y][$x - 1]['tile'], $horizontal, $nt))
						&& $matched['tile2-reoriented'][ opposite($vertical) ] === $matrix[$y - 1][$x]['tile'][ $vertical ])
						break ;
			}

			// Found the matching tile.
			if ($matched)
			{
				$matrix[$y][$x] = ['id' => $nextTile['id'], 'tile' => $matched['tile2-reoriented']];
				$x++;
				unset($tiles[$key]);
				continue 2;
			}
		}
		$y++;
		$x = 0;
	}
	return $matrix;
}

// Pick and extract a corner.
function	pick_corner(array &$tiles, array $corners): array
{
	// 1. Pick a corner.
	$corn = current($corners);
	$horz = (in_array($corn[0], ['e', 'w']) ? $corn[0] : $corn[1]);
	$vert = (in_array($corn[1], ['n', 's']) ? $corn[1] : $corn[0]);
	$id = key($corners);

	// 2. Find and extract the corner tile
	foreach ($tiles as $key => $t)
	{
		if ($t['id'] === $id)
		{
			$tile = $t;
			unset($tiles[$key]);
			break;
		}
	}

	// 3. Orientate correctly the corner.
	if ($horz === 'w' && $vert === 's')
		$tile = rotate_tile($tile);
	elseif ($horz === 'w' && $vert === 'n')
		$tile = rotate_tile(rotate_tile($tile));
	elseif ($horz === 'e' && $vert === 'n')
		$tile = rotate_tile(rotate_tile(rotate_tile($tile)));
	return ['id' => $id, 'tile' => $tile];
}

function	cmp_tiles(array $tile1, string $side, array $tile2)
{
	if (empty($tile1) || empty($tile2))
		return false;

	for ($i = 0; $i < 4; $i++)
	{
		if ($tile1[ $side ] === $tile2[ opposite($side) ])
			return ['tile2-reoriented' => $tile2];
		$tile2 = rotate_tile($tile2);
	}
	return false;
}

function	opposite(string $side): string
{
	return ['n' => 's', 's' => 'n', 'w' => 'e', 'e' => 'w'][ $side ];
}

function	rotate_tile(array $tile): array
{
	return [
		'n' => $tile['e'],
		'e' => strrev($tile['s']),
		's' => $tile['w'],
		'w' => strrev($tile['n']),
		'core' => rotate($tile['core'])
	];
}

function	rotate(array $tile): array
{
	$rotated = [];

	foreach (array_keys($tile) as $i)
		array_unshift($rotated, array_column($tile, $i));
	return $rotated;
}

function	flip(array $tile): array
{
	return [
		'n' => strrev($tile['n']),
		's' => strrev($tile['s']),
		'w' => $tile['e'],
		'e' => $tile['w'],
		'core' => array_map("array_reverse", $tile['core'])
	];
}

function	compose(array $tile): array
{
	$res = [];

	foreach (['normal' => $tile, 'flipped' => flip($tile)] as $state => $t)
	{
		for ($i = 0; $i < 4; $i++)
		{
			$res[ $state ]['n'][] = $t['n'];
			$res[ $state ]['s'][] = $t['s'];
			$res[ $state ]['e'][] = $t['e'];
			$res[ $state ]['w'][] = $t['w'];
			$t = rotate_tile($t);
		}
	}
	return $res;
}


function	parse(string $input): array
{
	$pattern = "/Tile\s(\d+):([\.|\#|\n]+)/";
	$data = [];

	preg_match_all($pattern, $input, $matches);
	foreach ($matches[2] as $id => $tile)
	{
		$tile = trim($tile);
		$core = [];

		foreach (explode("\n", $tile) as $i => $row)
			if ($i > 0 && $i < 9)
				$core[] = str_split(substr($row, 1, 8));
		
		preg_match_all("/.$/m", $tile, $m1);
		preg_match_all("/^./m", $tile, $m2);

		$up = substr($tile, 0, 10);
		$right = implode($m1[0]);
		$left = implode($m2[0]);
		$down = substr($tile, -10);

		$data[] = [
			'id' => intval($matches[1][$id]),
			'n' => $up,
			'e' => $right,
			'w' => $left,
			's' => $down,
			'core' => $core
		];
	}
	return $data;
}
