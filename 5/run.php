#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	// Build all the removal pairs required in this input.
	$removals = [];
	for ($unit = 65; $unit < 91; $unit++) {
		$removals[] = chr($unit) . chr($unit ^ 32);
		$removals[] = chr($unit ^ 32) . chr($unit);
	}

	function react($input) {
		global $removals;
		do {
			$input = str_replace($removals, '', $input, $count);
		} while ($count > 0);

		return $input;
	}

	$part1 = react($input);
	echo 'Part 1: ', strlen($part1), "\n";

	// Start smaller.
	$input = $part1;
	$shortest = strlen($input);
	for ($unit = 65; $unit < 91; $$unit++) {
		$newInput = str_replace([chr($unit), chr($unit ^ 32)], '', $input, $count);
		if ($count > 0) {
			$result = react($newInput);
			if (strlen($result) < $shortest) { $shortest = strlen($result); }
		}
	}

	echo 'Part 2: ', $shortest, "\n";
