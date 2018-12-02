#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$boxes = getInputLines();

	function hasMatchingCount($box, $count = 2) {
		$letters = [];
		foreach (str_split($box) as $letter) {
			if (!isset($letters[$letter])) { $letters[$letter] = 0; }
			$letters[$letter]++;
		}

		foreach ($letters as $l => $v) {
			if ($v == $count) { return true; }
		}

		return false;
	}

	$hasThree = 0;
	$hasTwo = 0;
	foreach ($boxes as $box) {
		if (hasMatchingCount($box, 2)) { $hasTwo++; }
		if (hasMatchingCount($box, 3)) { $hasThree++; }
	}

	$part1 = $hasThree * $hasTwo;

	echo 'Part 1: ', $part1, "\n";

