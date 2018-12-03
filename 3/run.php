#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	foreach ($input as $details) {
		preg_match('#^\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)$#SADi', $details, $m);
		list($all, $claim, $x, $y, $w, $h) = $m;
		$claims[$claim] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'overlap' => false);
	}

	$max = isTest() ? 10 : 1000;

	$fabric = [];
	foreach (yieldXY(0, 0, $max, $max) as $x => $y) {
		$fabric[$x][$y] = [];
	}

	foreach ($claims as $cid => $claim) {
		foreach (yieldXY($claim['x'], $claim['y'], $claim['x'] + $claim['w'] - 1, $claim['y'] + $claim['h'] - 1) as $x => $y) {
			$fabric[$x][$y][] = $cid;

			if (count($fabric[$x][$y]) > 1) {
				foreach ($fabric[$x][$y] as $cid) {
					$claims[$cid]['overlap'] = true;
				}
			}
		}
	}

	$part1 = 0;
	foreach (yieldXY(0, 0, $max, $max) as $x => $y) {
		if (count($fabric[$x][$y]) > 1) {
			$part1++;
		}
	}

	echo 'Part 1: ', $part1, "\n";

	foreach ($claims as $cid => $claim) {
		if ($claim['overlap'] === false) {
			echo 'Part 2: ', $cid, "\n";
		}
	}

