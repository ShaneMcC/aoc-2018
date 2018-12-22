#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$tX = $tY = $depth = 0;

	$entries = array();
	foreach ($input as $details) {
		if (preg_match('#depth: ([0-9]+)#SADi', $details, $m)) {
			$depth = $m[1];
		} else if (preg_match('#target: ([0-9]+),([0-9]+)#SADi', $details, $m)) {
			$tX = $m[1];
			$tY = $m[2];
		}
	}

	$grid[0][0] = 0;
	$grid[$tY][$tX] = 0;

	function getErosionLevel($x, $y) {
		global $grid, $depth;

		if (isset($grid[$y][$x])) {
			return ($grid[$y][$x] + $depth) % 20183;
		} else {
			throw new Exception('Unable to calculate erosion for: ' . $x . ',' . $y);
		}
	}

	function getGeologicIndex($x, $y) {
		global $grid;

		if (isset($grid[$y][$x])) { return $grid[$y][$x]; }

		if ($y == 0) {
			$grid[$y][$x] = $x * 16807;
			return $grid[$y][$x];
		} else if ($x == 0) {
			$grid[$y][$x] = $y * 48271;
			return $grid[$y][$x];
		} else {
			$grid[$y][$x] = getErosionLevel($x - 1, $y) * getErosionLevel($x, $y - 1);
		}
	}

	function draw() {
		global $grid, $tX, $tY;

		foreach ($grid as $y => $row) {
			foreach ($row as $x => $cell) {
				if ($x == 0 && $y == 0) { echo 'M'; continue; }
				if ($x == $tX && $y == $tY) { echo 'T'; continue; }

				$type = getErosionLevel($x, $y) % 3;

				if ($type == 0) { echo '.'; }
				else if ($type == 1) { echo '='; }
				else if ($type == 2) { echo '|'; }
			}
			echo "\n";
		}
		echo "\n";
	}

	function getRisk($sX, $sY, $eX, $eY) {
		global $grid;
		$risk = 0;

		for ($y = $sY; $y <= $eY; $y++) {
			for ($x = $sX; $x <= $eX; $x++) {
				$type = getErosionLevel($x, $y) % 3;

				$risk += $type;
			}
		}

		return $risk;
	}

	function calculateGrid() {
		global $grid, $tX, $tY;

		for ($y = 0; $y <= $tY + 5; $y++) {
			for ($x = 0; $x <= $tX + 5; $x++) {
				getGeologicIndex($x, $y);
			}
			ksort($grid[$y]);
		}
		ksort($grid);
	}

	calculateGrid();
	draw();

	echo 'Part 1: ', getRisk(0, 0, $tX, $tY), "\n";
