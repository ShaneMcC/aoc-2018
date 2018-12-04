#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$fabric = [];
	$part1 = 0;

	foreach ($input as $details) {
		preg_match('#\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)#SADi', $details, $m);
		list($all, $cid, $cx, $cy, $cw, $ch) = $m;

		$claims[$cid] = $cid;

		// Map out each use of fabric
		foreach (yieldXY($cx, $cy, $cx + $cw, $cy + $ch, false) as $x => $y) {
			// We use the $cid as a key for efficiency.
			$fabric[$x][$y][$cid] = true;

			// If there is more than one sub-item in the array then this is an
			// overlap.
			if (count($fabric[$x][$y]) > 1) {
				// If it is exactly 2, then this is the first time we have seen
				// this particular segment overlap, so count it for part 1.
				if (count($fabric[$x][$y]) == 2) { $part1++; }

				// Unset any known overlaps from $claims array.
				foreach (array_keys($fabric[$x][$y]) as $cid2) {
					unset($claims[$cid2]);
				}
			}
		}
	}

	// Count of overlaps.
	echo 'Part 1: ', $part1, "\n";

	// Output the first (should be only) claim that was never part of an overlap
	foreach ($claims as $cid) {
		echo 'Part 2: ', $cid, "\n";
		break;
	}
