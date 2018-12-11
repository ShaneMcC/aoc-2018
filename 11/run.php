#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$gridSerial = getInputLine();

	function getPowerLevel($x, $y, $gridSerial) {
		$rackID = $x + 10;
		$level = $rackID * $y;
		$level += $gridSerial;
		$level *= $rackID;
		$level = ($level / 100) % 10;
		$level -= 5;

		return $level;
	}

	// Generate prefix-sum grid.
	// https://en.wikipedia.org/wiki/Summed-area_table
	foreach (yieldXY(0, 0, 300, 300) as $x => $y) {
		// Prefix Sum is the power value of the whole square from 0,0 to x,y.

		// Calculate our value;
		$grid[$y][$x] = getPowerLevel($x, $y, $gridSerial);

		// Add the value above us if it exists.
		if (isset($grid[$y - 1][$x])) { $grid[$y][$x] += $grid[$y - 1][$x]; }

		// Add the value left of us if it exists.
		if (isset($grid[$y][$x - 1])) { $grid[$y][$x] += $grid[$y][$x - 1]; }

		// Because these both also include their above/left, that will add
		// above+left twice, so remove it.
		if (isset($grid[$y - 1][$x - 1])) { $grid[$y][$x] -= $grid[$y - 1][$x - 1]; }
	}

	function getMax($size = 3) {
		global $grid, $knownPowers;

		$maxLevel = $maxX = $maxY = 0;

		$lastLevel = NULL;
		foreach (yieldXY(1, 1, 300 - $size, 300 - $size) as $x => $y) {
			$level = 0;

			// Get the value of a square.
			// This is the value of our bottom right corner.
			$level = $grid[$y + $size - 1][$x + $size - 1];

			// Minus the value above our top-left corner.
			$level -= $grid[$y - 1][$x + $size - 1];

			// Minus the value left of our top-left corner.
			$level -= $grid[$y + $size - 1][$x - 1];

			// Re-Add top-left top-left corner because we removed it twice.
			$level += $grid[$y - 1][$x - 1];

			if ($level > $maxLevel) {
				$maxLevel = $level;
				$maxX = $x;
				$maxY = $y;
			}
		}

		return [$maxX, $maxY, $maxLevel];
	}

	list($maxX, $maxY, $maxLevel) = getMax(3);
	echo 'Part 1: ', $maxX, ',', $maxY, ' (', $maxLevel, ')', "\n";

	$maxLevel = $maxX = $maxY = $maxI = 0;
	for ($i = 1; $i < 300; $i++) {
		list($x, $y, $level) = getMax($i);

		if ($level > $maxLevel) {
			$maxLevel = $level;
			$maxX = $x;
			$maxY = $y;
			$maxI = $i;
		}
	}

	echo 'Part 2: ', $maxX, ',', $maxY, ',', $maxI, ' (', $maxLevel, ')', "\n";
