#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');

	$input = getInputLines();

	$maxX = $maxY = 0;
	$minX = $minY = PHP_INT_MAX;
	$safeAreaSize = (isTest() ? 32 : 10000);

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

	function getAreas($extra = 0) {
		global $minX, $minY, $maxX, $maxY, $safeAreaSize, $coords;

		$safeSize = 0;
		$areaSize = [];
		$grid = [];

		$myMinX = $minX - $extra;
		$myMinY = $minY - $extra;
		$myMaxX = $maxX + $extra;
		$myMaxY = $maxY + $extra;

		foreach (yieldXY($myMinX, $myMinY, $myMaxX, $myMaxY) as $x => $y) {
			$edge = in_array($x, [$myMinX, $myMaxX]) || in_array($y, [$myMinY, $myMaxY]);
			list($closest, $total) = getGridData($x, $y);
			$id = count($closest) == 1 ? $closest[0] : '';

			// Is this safe?
			if ($total < $safeAreaSize) {
				$safeSize++;

				// If the safe area touches the boundary, we need to expand our
				// search grid. Fuck you MD87...
				if ($edge) {
					return getAreas(ceil($safeAreaSize / count($coords)) + 1);
				}
			}

			// We only care about the area sizes for areas within the normal
			// boundary.
			if ($x >= $minX && $x <= $maxX && $y >= $minY && $y <= $maxY) {
				$grid[$y][$x] = $id;
				if ($id !== '') {
					if ($edge) {
						$areaSize[$id] = -1;
					} else {
						if (!isset($areaSize[$id])) { $areaSize[$id] = 0; }
						if ($areaSize[$id] >= 0) { $areaSize[$id]++; }
					}
				}
			}
		}

		return [$areaSize, $grid, $safeSize];
	}

	list($areaSize, $grid, $safeSize) = getAreas();

	if (isDebug()) {
		draw($areaSize);
	}

	echo 'Part 1: ', max($areaSize), "\n";
	echo 'Part 2: ', $safeSize, "\n";
