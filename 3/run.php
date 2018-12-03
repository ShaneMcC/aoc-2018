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

		foreach (yieldXY($cx, $cy, $cx + $cw, $cy + $ch, false) as $x => $y) {
			$fabric[$x][$y][$cid] = true;

			if (count($fabric[$x][$y]) > 1) {
				if (count($fabric[$x][$y]) == 2) { $part1++; }

				foreach ($fabric[$x][$y] as $cid2 => $_) {
					unset($claims[$cid2]);
				}
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";

	foreach ($claims as $cid) {
		echo 'Part 2: ', $cid, "\n";
	}
