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
		global $grid;

		$maxLevel = $maxX = $maxY = 0;

		foreach (yieldXY(1, 1, 300 - $size, 300 - $size) as $x => $y) {
			$level = 0;
			foreach (yieldXY($x, $y, $x + $size, $y + $size, false) as $x2 => $y2) {
				$level += $grid[$y2][$x2];
			}

			if ($level > $maxLevel) {
				$maxLevel = $level;
				$maxX = $x;
				$maxY = $y;
			}
		}

		return [$maxX, $maxY, $maxLevel];
	}

	[$maxX, $maxY, $maxLevel] = getMax(3);
	echo $maxLevel, "\n";
	echo $maxX, ', ', $maxY, "\n";

	echo "\n\n";

	$maxLevel = $maxX = $maxY = $maxI = 0;

	for ($i = 0; $i < 300; $i++) {
		[$x, $y, $level] = getMax($i);

		if ($level > $maxLevel) {
			$maxLevel = $level;
			$maxX = $x;
			$maxY = $y;
			$maxI = $i;

			echo $maxLevel, "\n";
			echo $maxX, ',', $maxY, ',', $i, "\n";
		}
	}
