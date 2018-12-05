#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$changes = getInputLines();

	function doFrequencyChanges($changes, &$freq = 0, &$knownValues = []) {
		foreach ($changes as $c) {
			$freq += $c;

			if (array_key_exists($freq, $knownValues)) { return true; }
			$knownValues[$freq] = true;
		}

		return false;
	}

	$part1 = $part2 = 0;
	$known = [0];
	doFrequencyChanges($changes, $part1);
	echo 'Part 1: ', $part1, "\n";

	while (true) {
		if (doFrequencyChanges($changes, $part2, $known)) {
			break;
		}
	}
	echo 'Part 2: ', $part2, "\n";
