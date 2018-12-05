#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function react($input) {
		// Possible removal units.
		$removals = [];
		foreach (array_keys(count_chars(strtolower($input), 1)) as $unit) {
			$removals[] = chr($unit) . chr($unit - 32);
			$removals[] = chr($unit - 32) . chr($unit);
		}

	    do {
	        $input = str_replace($removals, '', $input, $count);
	    } while ($count > 0);

	    return $input;
	}

	$part1 = react($input);
	echo 'Part 1: ', strlen($part1), "\n";

	// Start smaller.
	$input = $part1;
	$shortest = -1;
	foreach (array_keys(count_chars(strtolower($input), 1)) as $unit) {
		$newInput = preg_replace('#' . chr($unit) . '#i', '', $input);
		$result = react($newInput);
		if (strlen($result) < $shortest || $shortest == -1) { $shortest = strlen($result); }
	}

	echo 'Part 2: ', $shortest, "\n";
