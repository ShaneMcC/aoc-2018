#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	foreach ($input as $details) {
		preg_match('#^\#([0-9]+) @ ([0-9]+),([0-9]+): ([0-9]+)x([0-9]+)$#SADi', $details, $m);
		list($all, $claim, $x, $y, $w, $h) = $m;
		$claims[$claim] = array('x' => $x, 'y' => $y, 'w' => $w, 'h' => $h);
	}

	$max = isTest() ? 10 : 1000;

	$fabric = [];
	foreach (yieldXY(0, 0, $max, $max) as $x => $y) {
		$fabric[$x][$y] = '.';
	}

	foreach ($claims as $claim => $data) {
		foreach (yieldXY($data['x'], $data['y'], $data['x'] + $data['w'] - 1, $data['y'] + $data['h'] - 1) as $x => $y) {
			if ($fabric[$x][$y] == '.') {
				$fabric[$x][$y] = $claim;
			} else {
				$fabric[$x][$y] = 'X';
			}
		}
	}

	$part1 = 0;
	foreach (yieldXY(0, 0, $max, $max) as $x => $y) {
		if ($fabric[$x][$y] == 'X') {
			$part1++;
		}
	}

	if (isDebug()) {
		foreach ($fabric as $x => $y) {
			echo implode('', $y), "\n";
		}
	}

	echo 'Part 1: ', $part1, "\n";
