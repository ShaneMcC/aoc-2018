#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$fabric = [];
	$part1 = 0;

	foreach ($input as $details) {
		preg_match('#\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)#SADi', $details, $m);
		list($all, $cid, $x, $y, $w, $h) = $m;

		$claim = ['x' => $x, 'y' => $y, 'w' => $w, 'h' => $h];
		$claims[$cid] = $claim;

		foreach (yieldXY($claim['x'], $claim['y'], $claim['x'] + $claim['w'], $claim['y'] + $claim['h'], false) as $x => $y) {
			$fabric[$x][$y][$cid] = true;

			if (count($fabric[$x][$y]) > 1) {
				if (count($fabric[$x][$y]) == 2) { $part1++; }

				foreach (array_keys($fabric[$x][$y]) as $cid2) {
					unset($claims[$cid2]);
				}
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";

	foreach ($claims as $cid => $claim) {
		echo 'Part 2: ', $cid, "\n";
	}
