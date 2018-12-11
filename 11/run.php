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

	$maxLevel = $maxX = $maxY = 0;
	foreach (yieldXY(1, 1, 300 - 3, 300 - 3) as $x => $y) {
		$level = 0;
		$level += $grid[$y][$x];
		$level += $grid[$y][$x+1];
		$level += $grid[$y][$x+2];
		$level += $grid[$y+1][$x];
		$level += $grid[$y+1][$x+1];
		$level += $grid[$y+1][$x+2];
		$level += $grid[$y+2][$x];
		$level += $grid[$y+2][$x+1];
		$level += $grid[$y+2][$x+2];

		if ($level > $maxLevel) {
			$maxLevel = $level;
			$maxX = $x;
			$maxY = $y;
		}
	}


	echo $maxLevel, "\n";
	echo $maxX, ', ', $maxY, "\n";
