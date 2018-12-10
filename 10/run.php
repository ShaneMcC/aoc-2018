#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$points = [];
	foreach ($input as $line) {
		preg_match('#position=<\s?([-0-9]+), \s?([-0-9]+)> velocity=<\s?([-0-9]+), \s?([-0-9]+)>#SADi', $line, $m);
		list($all, $x, $y, $vx, $vy) = $m;
		$points[] = ['x' => (int)$x, 'y' => (int)$y, 'vx' => (int)$vx, 'vy' => (int)$vy];
	}

	function drawPoints($difference = 0) {
		global $points;

		$maxY = $maxX = $minY = $minX = 0;
		$current = [];

		foreach ($points as $point) {
			$x = $point['x'] += ($difference * $point['vx']);
			$y = $point['y'] += ($difference * $point['vy']);

			$current[$y][$x] = '#';
			$minX = min($minX, $x);
			$maxX = max($maxX, $x);
			$minY = min($minY, $y);
			$maxY = max($maxY, $y);
		}

		for ($y = $minY; $y <= $maxY; $y++) {
			for ($x = $minX; $x <= $maxX; $x++) {
				echo isset($current[$y][$x]) ? $current[$y][$x] : '.';
			}
			echo "\n";
		}
	}

	function advance() {
		global $points;

		$maxY = $maxX = $minY = $minX = 0;
		foreach ($points as $id => $point) {
			$points[$id]['x'] += $points[$id]['vx'];
			$points[$id]['y'] += $points[$id]['vy'];

			$minX = min($minX, $point['x']);
			$maxX = max($maxX, $point['x']);
			$minY = min($minY, $point['y']);
			$maxY = max($maxY, $point['y']);
		}

		return [$minX, $minY, $maxX, $maxY];
	}

	$lastHeight = $lastWidth = PHP_INT_MAX;

	for ($i = 0; $i < 50000; $i++) {
		[$minX, $minY, $maxX, $maxY] = advance();
		$width = $maxX - $minX;
		$height = $maxY - $minY;

		if ($width > $lastWidth || $height > $lastHeight) {
			drawPoints(-2);
			echo $i - 1;
			die();
		}

		$lastHeight = $height;
		$lastWidth = $width;
	}


