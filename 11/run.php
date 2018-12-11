#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$gridSerial = getInputLine();

	function getPowerLevel($x, $y, $gridSerial) {
		$rackID = $x + 10;
		$level = $rackID * $y;
		$level += $gridSerial;
		$level *= $rackID;

		$level = strrev($level);
		$level = strlen($level) > 2 ? $level{2} : '0';

		$level -= 5;

		return $level;
	}

	foreach (yieldXY(1, 1, 300, 300) as $x => $y) {
		$grid[$y][$x] = getPowerLevel($x, $y, $gridSerial);
	}

	function getMax($size = 3) {
		global $grid, $knownPowers;

		$maxLevel = $maxX = $maxY = 0;

		$lastLevel = NULL;
		foreach (yieldXY(1, 1, 300 - $size, 300 - $size) as $x => $y) {
			$level = 0;

			if (isset($knownPowers[$size - 1][$x][$y])) {
				// Old level for 1-smaller grid.
				$level += $knownPowers[$size - 1][$x][$y];

				// Add the new Y Column
				for ($x2 = $x; $x2 < $x + $size; $x2++) {
					$level += $grid[$y + $size - 1][$x2];
				}

				// Add the new X Row excluding the corner.
				for ($y2 = $y; $y2 < $y + $size - 1; $y2++) {
					$level += $grid[$y2][$x + $size - 1];
				}
			} else {
				// Calculate the whole thing.
				foreach (yieldXY($x, $y, $x + $size, $y + $size, false) as $x2 => $y2) {
					$level += $grid[$y2][$x2];
				}
			}

			$knownPowers[$size][$x][$y] = $level;

			if ($level > $maxLevel) {
				$maxLevel = $level;
				$maxX = $x;
				$maxY = $y;
			}
		}

		unset($knownPowers[$size - 1]);

		return [$maxX, $maxY, $maxLevel];
	}

	list($maxX, $maxY, $maxLevel) = getMax(3);
	echo 'Part 1: ', $maxX, ',', $maxY, ' (', $maxLevel, ')', "\n";

	$maxLevel = $maxX = $maxY = $maxI = 0;
	for ($i = 1; $i < 300; $i++) {
		if (isDebug()) { echo $i, ' '; }
		[$x, $y, $level] = getMax($i);

		if ($level > $maxLevel) {
			$maxLevel = $level;
			$maxX = $x;
			$maxY = $y;
			$maxI = $i;
		}
	}

	echo 'Part 2: ', $maxX, ',', $maxY, ',', $maxI, ' (', $maxLevel, ')', "\n";
