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

		$maxY = $maxX = PHP_INT_MIN;
		$minY = $minX = PHP_INT_MAX;
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

		$word = '';
	}

	function advance() {
		global $points;

		$maxY = $maxX = $minY = $minX = 0;
		foreach ($points as $id => $point) {
			$points[$id]['x'] += $points[$id]['vx'];
			$points[$id]['y'] += $points[$id]['vy'];

			$minX = min($minX, $points[$id]['x']);
			$maxX = max($maxX, $points[$id]['x']);
			$minY = min($minY, $points[$id]['y']);
			$maxY = max($maxY, $points[$id]['y']);
		}

		return [$minX, $minY, $maxX, $maxY];
	}

	$lastHeight = $lastWidth = PHP_INT_MAX;

	for ($i = 0; true; $i++) {
		[$minX, $minY, $maxX, $maxY] = advance();
		$width = $maxX - $minX;
		$height = $maxY - $minY;

		if ($width > $lastWidth || $height > $lastHeight) {
			$word = drawPoints(-1);
			echo 'Part 1: ', $word, "\n";
			echo 'Part 2: ', $i, "\n";
			die();
		}

		$lastHeight = $height;
		$lastWidth = $width;
	}


