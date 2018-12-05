#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$changes = getInputLines();

	function doFrequencyChanges($changes, &$freq = 0, &$knownValues = []) {
		foreach ($changes as $c) {
			$freq += $c;

			if (array_key_exists($freq, $knownValues)) { return true; }
			$knownValues[$freq] = $freq;
		}

		return false;
	}

	$freq = 0;
	$known = [0 => 0];
	doFrequencyChanges($changes, $freq, $known);
	echo 'Part 1: ', $freq, "\n";

	array_pop($known);
	for ($i = 1; true; $i++) {
		foreach ($known as $k) {
			$f = $k + ($freq * $i);
			if (array_key_exists($f, $known)) {
				echo 'Part 2: (', $i, ') ', $f, "\n";
				break 2;
			}
		}
	}
