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
		return abs($x1 - $x2) + abs($y1 - $y2) + abs($z1 - $z2);
	}

	function inRange($entries, $x, $y, $z, $range = null) {
		global $entries;

		$result = 0;
		foreach ($entries as $e) {
			$m = manhattan3($x, $y, $z, $e['x'], $e['y'], $e['z']);

			if ($m <= ($range == NULL ? $e['range'] : $range)) {
				$result++;
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


		$dist = floor(min([($maxX - $minX) / 2, ($maxY - $minY) / 2, ($maxZ - $minZ) / 2]));

		$bestRange = 0;
		$best = [0, 0, 0];
		$bestM3 = PHP_INT_MAX;

		while (true) {
			for ($x = $minX; $x <= $maxX; $x += $dist) {
				for ($y = $minY; $y <= $maxY; $y += $dist) {
					for ($z = $minZ; $z <= $maxZ; $z += $dist) {
						$inRange = inRange($entries, $x, $y, $z);

						if ($inRange >= $bestRange) {
							$m3 = manhattan3($x, $y, $z, 0, 0, 0);

							if ($inRange > $bestRange || $m3 < $bestM3) {
								$bestRange = $inRange;
								$bestM3 = $m3;
								$best = [$x, $y, $z];
							}
						}

					}
				}
			}

			if ($dist <= 1) {
				return [$best, $bestRange, $bestM3];
			} else {
				list($minX, $maxX) = [$best[0] - $dist, $best[0] + $dist];
				list($minY, $maxY) = [$best[1] - $dist, $best[1] + $dist];
				list($minZ, $maxZ) = [$best[2] - $dist, $best[2] + $dist];

				$dist = floor($dist / 2);
			}
		}
	}

	$part1 = inRange($entries, $entries[$strongest]['x'], $entries[$strongest]['y'], $entries[$strongest]['z'], $entries[$strongest]['range']);
	echo 'Part 1: ', $part1, "\n";

	[$best, $bestRange, $bestM3] = calculateBestPoint($entries);
	echo 'Part 2: ', $bestM3, ' (',implode(',', $best), ' has ', $bestRange, ' bots in range.)', "\n";
