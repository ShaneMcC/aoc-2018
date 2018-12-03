#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	foreach ($input as $details) {
		preg_match('#\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)#SADi', $details, $m);
		list($all, $cid, $x, $y, $w, $h) = $m;
		$claims[$cid] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h);
	}

	$max = isTest() ? 10 : 1000;

	$fabric = [];

	$part1 = 0;
	foreach ($claims as $cid => $claim) {
		foreach (yieldXY($claim['x'], $claim['y'], $claim['x'] + $claim['w'] - 1, $claim['y'] + $claim['h'] - 1) as $x => $y) {
			$fabric[$x][$y][$cid] = true;

			if (count($fabric[$x][$y]) > 1) {
				if (count($fabric[$x][$y]) == 2) { $part1++; }

				foreach (array_keys($fabric[$x][$y]) as $cid) {
					unset($claims[$cid]);
				}
			}
		}
	}

	echo 'Part 1: ', $part1, "\n";

	foreach ($claims as $cid => $claim) {
		echo 'Part 2: ', $cid, "\n";
	}
