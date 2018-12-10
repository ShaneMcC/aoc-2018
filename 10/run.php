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

	function getOutput($difference = 0, $draw = false) {
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
		if (!isset($encodedChars[$id])) { echo 'Unknown Letter: ', $id, "\n"; }
		return isset($encodedChars[$id]) ? $encodedChars[$id] : '?';
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
			$word = getOutput(-1, isTest() || isDebug());
			echo 'Part 1: ', $word, "\n";
			echo 'Part 2: ', $i, "\n";
			die();
		}

		$lastHeight = $height;
		$lastWidth = $width;
	}
