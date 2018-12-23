#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	$strongest = $id = 0;
	foreach ($input as $details) {
		preg_match('#pos=<(.*),(.*),(.*)>, r=(.*)#SADi', $details, $m);
		list($all, $x, $y, $z, $range) = $m;

		$entries[$id] = ['x' => $x, 'y' => $y, 'z' => $z, 'range' => $range];
		if ($range > $entries[$strongest]['range']) {
			$strongest = $id;
		}
		$id++;
	}


	function manhattan3($x1, $y1, $z1, $x2, $y2, $z2) {
		return abs($x1 - $x2) + abs($y1 - $y2) + abs($z1 - $z2);
	}

	$part1 = 0;
	foreach ($entries as $e) {
		$m = manhattan3($entries[$strongest]['x'], $entries[$strongest]['y'], $entries[$strongest]['z'], $e['x'], $e['y'], $e['z']);

		if ($m <= $entries[$strongest]['range']) {
			$part1++;
		}
	}

	echo 'Part 1: ', $part1, "\n";
