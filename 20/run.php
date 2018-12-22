#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/pathfinder.php');

	$input = getInputLine();

	function addExit($x, $y, $directon) {
		global $grid;

		if (!isset($grid[$y][$x])) { $grid[$y][$x] = ['exits' => []]; }
		$grid[$y][$x]['exits'][] = $directon;
		if (isDebug()) { echo "\t", 'Add Direction: ', $x, ',', $y, ' => ', $directon; }

		if ($directon == 'N') { $result = [$x, $y - 1]; $directon = 'S'; }
		else if ($directon == 'S') { $result = [$x, $y + 1]; $directon = 'N'; }
		else if ($directon == 'E') { $result = [$x + 1, $y]; $directon = 'W'; }
		else if ($directon == 'W') { $result = [$x - 1, $y]; $directon = 'E'; }

		list($x2, $y2) = $result;
		if (isDebug()) { echo ' to ', $x2, ',', $y2, "\n"; }


		if (!isset($grid[$y2][$x2])) { $grid[$y2][$x2] = ['exits' => []]; }
		$grid[$y2][$x2]['exits'][] = $directon;

		return $result;
	}

	function parseInput($startX, $startY, $input) {
		global $grid;
		$subCount = 0;
		$subInput = '';

		list($x, $y) = [$startX, $startY];
		$endings = [];

		if (isDebug()) { echo 'Starting at: ', $startX, ',', $startY, ' with: ', $input, "\n"; }

		for ($i = 0; $i < strlen($input); $i++) {
			$d = $input{$i};

			if ($d == '^') { continue; }
			if ($d == '$') { break; }

			if ($d == '(') {
				if ($subCount != 0) { $subInput .= $d; }
				$subCount++;
			} else if ($d == ')') {
				$subCount--;

				if ($subCount == 0) {
					$ends = parseInput($x, $y, $subInput);
					$subInput = '';

					if (count($ends) > 1) {
						// TODO: Should all following instructions be applied
						// on top of all of these ends?
					}
				} else {
					$subInput .= $d;
				}
			} else if ($subCount == 0) {
				if ($d == 'N' || $d == 'S' || $d == 'E' || $d == 'W') {
					list($x, $y) = addExit($x, $y, $d);
				} else if ($d == '|') {
					$endings[] = $x . ',' . $y;
					if (isDebug()) { echo 'Ended at: ', $x, ',', $y, "\n"; }
					if (isDebug()) { echo 'Re-starting at: ', $startX, ',', $startY, "\n"; }
					list($x, $y) = [$startX, $startY];
				}
			} else {
				$subInput .= $d;
			}
		}

		$endings[] = $x . ',' . $y;

		if (isDebug()) { echo 'Ended at: ', $x, ',', $y, "\n"; }

		return array_unique($endings);
	}

	function getBounds($grid) {
		$minX = $minY = PHP_INT_MAX;
		$maxX = $maxY = PHP_INT_MIN;

		foreach ($grid as $y => $row) {
			$minY = min($minY, $y);
			$maxY = max($maxY, $y);
			foreach ($row as $x => $cell) {
				$minX = min($minX, $x);
				$maxX = max($maxX, $x);
			}
		}

		return [$minX, $minY, $maxX, $maxY];
	}

	function draw($grid, $highlight = []) {
		if (!is_array($grid)) { return; }

		list($minX, $minY, $maxX, $maxY) = getBounds($grid);

		// Header
		echo '#';
		for ($x = $minX; $x <= $maxX; $x++) { echo '##'; }
		echo "\n";

		for ($y = $minY; $y <= $maxY; $y++) {
			$firstRow = '#';
			$secondRow = '#';

			for ($x = $minX; $x <= $maxX; $x++) {
				$unknown = '@';
				$room = ($y == 0 && $x == 0) ? 'X' : '.';
				$wall = '#';
				$eastWestDoor = '|';
				$northSouthDoor = '-';

				if (isset($grid[$y][$x])) {
					if (in_array([$x, $y], $highlight)) {
						$room = ($y == 0 && $x == 0) ? 'X' : '*';
						$room = "\033[1;31m" . $room . "\033[0m";
						if (in_array([$x + 1, $y], $highlight)) {
							$eastWestDoor = "\033[1;31m" . '/' . "\033[0m";
						}
						if (in_array([$x, $y + 1], $highlight)) {
							$northSouthDoor = "\033[1;31m" . '/' . "\033[0m";
						}
					}

					$firstRow .= $room;
					$firstRow .= in_array('E', $grid[$y][$x]['exits']) ? $eastWestDoor : $wall;

					$secondRow .= in_array('S', $grid[$y][$x]['exits']) ? $northSouthDoor : $wall;
					$secondRow .= $wall;
				} else {
					$firstRow .= $unknown . $wall;
					$secondRow .= $wall . $wall;
				}
			}
			echo $firstRow, "\n", $secondRow, "\n";
		}

		echo "\n";
	}

	function getExitPoints($grid, $x, $y) {
		$points = [];

		$exits = $grid[$y][$x]['exits'];

		if (in_array('N', $exits)) { $points[] = [$x, $y - 1]; }
		if (in_array('S', $exits)) { $points[] = [$x, $y + 1]; }
		if (in_array('E', $exits)) { $points[] = [$x + 1, $y]; }
		if (in_array('W', $exits)) { $points[] = [$x - 1, $y]; }

		return $points;
	};

	function getCosts($grid) {
		$costs = [];

		$loc = [0, 0];
		$state = [[$loc, 0]];
		$costs[$loc[1]][$loc[0]] = ['cost' => 0, 'path' => []];

		$maxCost = PHP_INT_MAX;

		while (!empty($state)) {
			list($cur, $cost) = array_shift($state);

			foreach (getExitPoints($grid, $cur[0], $cur[1]) as $s) {
				if (!isset($costs[$s[1]][$s[0]])) {
					$state[] = [$s, ($cost + 1)];
					$old = $costs[$cur[1]][$cur[0]];
					$costs[$s[1]][$s[0]] = ['cost' => ($cost + 1), 'path' => $old['path']];
					$costs[$s[1]][$s[0]]['path'][] = $s;
				}
			}
		}

		return $costs;
	}


	parseInput(0, 0, $input);
	$costs = getCosts($grid);

	$furthest = NULL;
	foreach ($costs as $y => $row) {
		foreach ($row as $c => $cell) {
			if ($furthest == NULL || $cell['cost'] > $furthest['cost']) {
				$furthest = $cell;
			}
		}
	}

	if (isDebug()) { draw($grid, $furthest['path']); }
	echo 'Part 1: ', $furthest['cost'], "\n";
