#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$fabric = [];
	$part1 = 0;

	foreach ($input as $details) {
		preg_match('#\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)#SADi', $details, $m);
		list($all, $cid, $cx, $cy, $cw, $ch) = $m;

		$claims[$cid] = false;

		// Map out each use of fabric
		foreach (yieldXY($cx, $cy, $cx + $cw, $cy + $ch, false) as $x => $y) {
			if (!isset($fabric[$x][$y])) {
				$fabric[$x][$y] = $cid;
			} else {
				if (!is_array($fabric[$x][$y])) {
					unset($claims[$fabric[$x][$y]]);
					$fabric[$x][$y] = [$fabric[$x][$y] => true];
					$part1++;
				}

				$fabric[$x][$y][$cid] = true;
				unset($claims[$cid]);
			}
		}
	}


	// Count of overlaps.
	echo 'Part 1: ', $part1, "\n";

	$part2 = array_reduce(array_keys($claims), function($carry, $key) use ($claims) { return $claims[$key] ? $carry : $key; });
	echo 'Part 2: ', $part2, "\n";
