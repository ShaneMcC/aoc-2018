#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$tX = $tY = $depth = 0;

	$entries = array();
	foreach ($input as $details) {
		if (preg_match('#depth: ([0-9]+)#SADi', $details, $m)) {
			$depth = $m[1];
		} else if (preg_match('#target: ([0-9]+),([0-9]+)#SADi', $details, $m)) {
			$tX = $m[1];
			$tY = $m[2];
		}
	}

	$grid[0][0] = 0;
	$grid[$tY][$tX] = 0;

	function getErosionLevel($x, $y) {
		global $grid, $depth;

		if (isset($grid[$y][$x])) {
			return ($grid[$y][$x] + $depth) % 20183;
		} else {
			throw new Exception('Unable to calculate erosion for: ' . $x . ',' . $y);
		}
	}

	function getGeologicIndex($x, $y) {
		global $grid;

		if (isset($grid[$y][$x])) { return $grid[$y][$x]; }

		if ($y == 0) {
			$grid[$y][$x] = $x * 16807;
			return $grid[$y][$x];
		} else if ($x == 0) {
			$grid[$y][$x] = $y * 48271;
			return $grid[$y][$x];
		} else {
			$grid[$y][$x] = getErosionLevel($x - 1, $y) * getErosionLevel($x, $y - 1);
		}
	}

	function getRegionType($x, $y) {
		return getErosionLevel($x, $y) % 3;
	}

	function draw() {
		global $grid, $tX, $tY;

		foreach ($grid as $y => $row) {
			foreach ($row as $x => $cell) {
				if ($x == 0 && $y == 0) { echo 'M'; continue; }
				if ($x == $tX && $y == $tY) { echo 'T'; continue; }

				$type = getRegionType($x, $y);

				if ($type == 0) { echo '.'; }
				else if ($type == 1) { echo '='; }
				else if ($type == 2) { echo '|'; }
			}
			echo "\n";
		}
		echo "\n";
	}

	function getRisk($sX, $sY, $eX, $eY) {
		global $grid;
		$risk = 0;

		for ($y = $sY; $y <= $eY; $y++) {
			for ($x = $sX; $x <= $eX; $x++) {
				$type = getRegionType($x, $y);

				$risk += $type;
			}
		}

		return $risk;
	}

	function calculateGrid() {
		global $grid, $tX, $tY;

		$buffer = 100;

		for ($y = 0; $y <= $tY + $buffer; $y++) {
			for ($x = 0; $x <= $tX + $buffer; $x++) {
				getGeologicIndex($x, $y);
			}
			ksort($grid[$y]);
		}
		ksort($grid);
	}

	calculateGrid();

	echo 'Part 1: ', getRisk(0, 0, $tX, $tY), "\n";


	function getSurrounding($x, $y) {
		$locations = [];

		$locations[] = [$x, $y - 1];
		$locations[] = [$x - 1, $y];
		$locations[] = [$x + 1, $y];
		$locations[] = [$x, $y + 1];

		return $locations;
	}

	function getCosts() {
		global $grid;

		$validTools = [0 => ['C', 'T'], 1 => ['C', 'N'], 2 => ['T', 'N']];

		$costs = [];

		$queue = new SPLPriorityQueue();
		$queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
		$queue->insert([0, 0, 'T'], 0);

		while (!$queue->isEmpty()) {
			$q = $queue->extract();

			list($x, $y, $tool) = $q['data'];

			// SPLPriorityQueue treats higher numbers as higher priority,
			// so we using negatives when we insert, so get the real value here.
			$cost = abs($q['priority']);

			// If we've visited here before then this is a longer-cost path so
			// we can ignore it.
			if (isset($costs[$y][$x][$tool])) { continue; }

			$costs[$y][$x][$tool] = $cost;

			$type = getRegionType($x, $y);

			// Try and visit anywhere that we can visit with our current tool.
			foreach (getSurrounding($x, $y) as $p) {
				list($pX, $pY) = $p;

				// If it's valid...
				if (!isset($grid[$pY][$pX])) { continue; }
				$pType = getRegionType($pX, $pY);

				if (in_array($tool, $validTools[$pType])) {
					$queue->insert([$pX, $pY, $tool], -($cost + 1));
				}
			}

			// Also try changing tool here and visiting ourselves.
			foreach ($validTools[$type] as $t) {
				if ($tool != $t) {
					$queue->insert([$x, $y, $t], -($cost + 7));
				}
			}
		}

		return $costs;
	}

	$costs = getCosts();

	echo 'Part 2: ', $costs[$tY][$tX]['T'], "\n";
