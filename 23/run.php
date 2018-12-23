#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	$strongest = $id = 0;

	foreach ($input as $details) {
		preg_match('#pos=<(.*),(.*),(.*)>, r=(.*)#SADi', $details, $m);
		list($all, $x, $y, $z, $range) = $m;

		$entries[$id] = ['x' => $x, 'y' => $y, 'z' => $z, 'range' => $range];
		if ($range > $entries[$strongest]['range']) {
			$strongest = $id;
		}
		$id++;
	}


	function manhattan3($x1, $y1, $z1, $x2, $y2, $z2) {
		return intval(abs($x1 - $x2)) + intval(abs($y1 - $y2)) + intval(abs($z1 - $z2));
	}

	function inRange($entries, $x, $y, $z, $range = null, $scale = 1, $required = 0) {
		global $entries;

		$available = count($entries);

		$result = 0;
		foreach ($entries as $e) {
			$m = manhattan3($x, $y, $z, $e['x'] / $scale, $e['y'] / $scale, $e['z'] / $scale);

			if ($m <= ($range == NULL ? $e['range'] / $scale : $range / $scale)) {
				$result++;
			} else {
				$available--;
				if ($available < $required) { break; }
			}
		}

		return $result;
	}

	// Binary search over the space to find the best point.
	function calculateBestPoint($entries) {
		$minX = array_reduce($entries, function($c, $i) { return min($c, $i['x']); }, PHP_INT_MAX);
		$maxX = array_reduce($entries, function($c, $i) { return max($c, $i['x']); }, PHP_INT_MIN);

		$minY = array_reduce($entries, function($c, $i) { return min($c, $i['y']); }, PHP_INT_MAX);
		$maxY = array_reduce($entries, function($c, $i) { return max($c, $i['y']); }, PHP_INT_MIN);

		$minZ = array_reduce($entries, function($c, $i) { return min($c, $i['z']); }, PHP_INT_MAX);
		$maxZ = array_reduce($entries, function($c, $i) { return max($c, $i['z']); }, PHP_INT_MIN);

		$scale = 10000000;

		while (true) {
			$bestRange = 0;
			$bestM3 = PHP_INT_MAX;
			$best = [0, 0, 0];

			$rangeX = [intval($minX / $scale), intval($maxX / $scale)];
			$rangeY = [intval($minY / $scale), intval($maxY / $scale)];
			$rangeZ = [intval($minZ / $scale), intval($maxZ / $scale)];

			if (isDebug()) {
				echo 'Scale: ', $scale, "\n";
				echo "\t", $rangeX[0], '...', $rangeX[1], ', ', $rangeY[0], '...', $rangeY[1], ', ', $rangeZ[0], '...', $rangeZ[1], "\n";
			}

			for ($x = $rangeX[0]; $x <= $rangeX[1]; $x++) {
				for ($y = $rangeY[0]; $y <= $rangeY[1]; $y++) {
					for ($z = $rangeZ[0]; $z <= $rangeZ[1]; $z++) {
						$inRange = inRange($entries, $x, $y, $z, null, $scale, $bestRange);

						if ($inRange >= $bestRange) {
							$m3 = manhattan3($x, $y, $z, 0, 0, 0);

							if ($inRange > $bestRange || $m3 < $bestM3) {
								$bestRange = $inRange;
								$bestM3 = $m3;
								$best = [$x, $y, $z];

								// if (isDebug()) { echo "\t\t", $bestRange, ' => ', implode(',', $best), "\n"; }
							}
						}

					}
				}
			}

			if (isDebug() && $bestRange > 0) {
				echo "\t", 'Best Range: ', $bestRange, "\n";
				echo "\t", 'Best: ', implode(',', $best), "\n";
				echo "\t", 'BestM3: ', $bestM3, "\n";
			}

			$padding = 3;
			list($minX, $maxX) = [($best[0] - $padding) * $scale, ($best[0] + $padding) * $scale];
			list($minY, $maxY) = [($best[1] - $padding) * $scale, ($best[1] + $padding) * $scale];
			list($minZ, $maxZ) = [($best[2] - $padding) * $scale, ($best[2] + $padding) * $scale];

			if ($scale == 1) {
				return [$best, $bestRange, $bestM3];
			}

			$scale /= 10;
		}
	}

	$part1 = inRange($entries, $entries[$strongest]['x'], $entries[$strongest]['y'], $entries[$strongest]['z'], $entries[$strongest]['range']);
	echo 'Part 1: ', $part1, "\n";

	list($best, $bestRange, $bestM3) = calculateBestPoint($entries);
	echo 'Part 2: ', $bestM3, ' (',implode(',', $best), ' has ', $bestRange, ' bots in range.)', "\n";
