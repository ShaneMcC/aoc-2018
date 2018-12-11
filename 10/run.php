#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$encodedChars = array (
	  '3048848484fc84848484' => 'A',
	  'f8848484f884848484f8' => 'B',
	  '78848080808080808478' => 'C',
	  'fc808080f880808080fc' => 'E',
	  'fc808080f88080808080' => 'F',
	  '78848080809c84848c74' => 'G',
	  '84848484fc8484848484' => 'H',
	  '1c080808080808888870' => 'J',
	  '848890a0c0c0a0908884' => 'K',
	  '808080808080808080fc' => 'L',
	  '84c4c4a4a494948c8c84' => 'N',
	  'f8848484f88080808080' => 'P',
	  'f8848484f89088888484' => 'R',
	  '84844848303048488484' => 'X',
	  'fc0404081020408080fc' => 'Z',
	);

	$points = [];
	foreach ($input as $line) {
		preg_match('#position=<\s?([-0-9]+), \s?([-0-9]+)> velocity=<\s?([-0-9]+), \s?([-0-9]+)>#SADi', $line, $m);
		list($all, $x, $y, $vx, $vy) = $m;
		$points[] = ['x' => (int)$x, 'y' => (int)$y, 'vx' => (int)$vx, 'vy' => (int)$vy];
	}

	// Based on https://github.com/MageJohn/Advent-of-Code-2018/blob/master/Day%2010%3A%20The%20Stars%20Align/Day%2010%20Solutions.ipynb
	function guessOptimalTime() {
		global $points;

		// Maximum velocity.
		$dy = max(array_column($points, 'vy'));

		// Calculate minimum points that have the opposite min/max velocities.
		$y1 = $y2 = PHP_INT_MAX;
		foreach ($points as $p) {
			if ($p['vy'] == $dy) {
				$y1 = min($y1, $p['y']);
			}
			if ($p['vy'] == 0 - $dy) {
				$y2 = min($y2, $p['y']);
			}
		}

		// Guess a time.
		$time = ($y2 - $y1)/(2 * $dy);

		return $time;
	}



	function getAt($time) {
		global $points;

		$maxY = $maxX = PHP_INT_MIN;
		$minY = $minX = PHP_INT_MAX;

		$pointsAt = [];
		foreach ($points as $id => $point) {
			$x = $point['x'] += ($time * $point['vx']);
			$y = $point['y'] += ($time * $point['vy']);
			$pointsAt[] = [$x, $y];

			$minX = min($minX, $x);
			$maxX = max($maxX, $x);
			$minY = min($minY, $y);
			$maxY = max($maxY, $y);
		}

		return [$minX, $minY, $maxX, $maxY, $pointsAt];
	}

	function getOutput($time = 0, $draw = false) {
		global $points;

		$current = [];
		[$minX, $minY, $maxX, $maxY, $pointsAt] = getAt($time);
		foreach ($pointsAt as $point) { $current[$point[1]][$point[0]] = '#'; }
		$characters = [];

		$letterWidth = 8;
		// Make sure we are the right width for grabbing letters.
		$maxX += $letterWidth - (($maxX + 1 - $minX) % $letterWidth);

		for ($y = $minY; $y <= $maxY; $y++) {
			for ($x = $minX; $x <= $maxX; $x++) {
				$out = isset($current[$y][$x]) ? $current[$y][$x] : '.';
				if ($draw) { echo $out; }

				$c = (int)(($x - $minX) / $letterWidth);
				$characters[$c][$y - $minY][] = $out;
			}
			if ($draw) { echo "\n"; }
		}

		$result = '';
		foreach ($characters as $character) { $result .= charToLetter($character); }
		return $result;
	}

	function charToID($character) {
		$id = '';
		foreach ($character as $bit) {
			if (is_array($bit)) { $bit = implode('', $bit); }
			$id .= sprintf('%02s', dechex(bindec(str_replace(['.', '#'], [0, 1], str_pad($bit, '8', '.')))));
		}
		return $id;
	}

	function charToLetter($character) {
		global $encodedChars;
		$id = charToID($character);
		if (isDebug() && !isset($encodedChars[$id])) { echo 'Unknown Letter: ', $id, "\n"; }
		return isset($encodedChars[$id]) ? $encodedChars[$id] : '?';
	}

	$lastHeight = $lastWidth = PHP_INT_MAX;

	$guess = guessOptimalTime();
	foreach ([$guess, $guess - 1, $guess + 1] as $i) {
		$word = getOutput($i, isTest() || isDebug());
		if (str_replace('?', '', $word) != '') {
			echo 'Part 1: ', $word, "\n";
			echo 'Part 2: ', $i, "\n";
			die();
		}
	}

	for ($i = 0; true; $i++) {
		[$minX, $minY, $maxX, $maxY, $pointsAt] = getAt($i);
		$width = $maxX - $minX;
		$height = $maxY - $minY;

		if ($width > $lastWidth || $height > $lastHeight) {
			$word = getOutput($i - 1, isTest() || isDebug());
			echo 'Part 1: ', $word, "\n";
			echo 'Part 2: ', $i - 1, "\n";
			die();
		}

		$lastHeight = $height;
		$lastWidth = $width;
	}
