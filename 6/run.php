#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');

	$input = getInputLines();

	$maxX = $maxY = 0;
	$minX = $minY = PHP_INT_MAX;

	$coords = [];
	foreach ($input as $details) {
		preg_match('#([0-9]+), ([0-9]+)#SADi', $details, $m);
		list($all, $x, $y) = $m;

		$coords[] = ['x' => $x, 'y' => $y];
		if ($x > $maxX) { $maxX = $x; }
		if ($y > $maxY) { $maxY = $y; }
		if ($x < $minX) { $minX = $x; }
		if ($y < $minY) { $minY = $y; }
	}

	function manhattan($x1, $y1, $x2, $y2) {
		return abs($x1 - $x2) + abs($y1 - $y2);
	}

	function getGridData($x, $y) {
		global $coords;

		$total = 0;

		$closestDistance = PHP_INT_MAX;
		$closest = [];
		foreach ($coords as $id => $c) {
			$distance = manhattan($x, $y, $c['x'], $c['y']);
			$total += $distance;

			if ($distance < $closestDistance) {
				$closest = [$id];
				$closestDistance = $distance;
			} else if ($distance == $closestDistance) {
				$closest[] = $id;
			}
		}

		return [$closest, $total];
	}

	$safeSize = 0;
	$areaSize = [];
	$grid = [];
	foreach (yieldXY($minX, $minY, $maxX, $maxY) as $x => $y) {
		[$closest, $total] = getGridData($x, $y);
		$id = count($closest) == 1 ? $closest[0] : '.';

		$grid[$y][$x] = $id;
		if ($id != '.') {
			if (!isset($areaSize[$id])) { $areaSize[$id] = 0; }
			$areaSize[$id]++;
		}

		if ($total < (isTest() ? 32 : 10000)) { $safeSize++; }
	}

	// Remove the infinite ones (any that touch an edge)
	foreach (yieldXY($minX, $minY, $maxX, $maxY) as $x => $y) {
		if (!in_array($x, [$minX, $maxX]) && !in_array($y, [$minY, $maxY])) { continue; }

		$id = $grid[$y][$x];

		if (isset($areaSize[$id])) {
			if (isDebug()) { echo 'Removing: ', $id, "\n"; }
			unset($areaSize[$id]);
		}
	}


	function draw() {
		global $grid;

		foreach ($grid as $row) {
			$first = true;
			foreach ($row as $item) {
				if (!$first) { echo ','; } else { $first = false; }
				echo sprintf('%3s', $item);
			}
			echo "\n";
		}
	}

	if (isDebug()) {
		draw();
		asort($areaSize);
		var_dump($areaSize);
	}

	echo 'Part 1: ', max($areaSize), "\n";
	echo 'Part 2: ', $safeSize, "\n";
