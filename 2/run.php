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

	function getDifferent($box1, $box2) {
		$box1 = str_split($box1);
		$box2 = str_split($box2);

		$same = [];
		$differentCount = 0;
		for ($i = 0; $i < count($box1); $i++) {
			if ($box1[$i] == $box2[$i]) {
				$same[] = $box1[$i];
			} else {
				$differentCount++;
			}
		}

		return [$differentCount, implode('', $same)];
	}

	foreach ($boxes as $box1) {
		foreach ($boxes as $box2) {
			$result = getDifferent($box1, $box2);

			if ($result[0] == 1) {
				echo 'Part 2: ', $result[1], "\n";
				die();
			}
		}
	}


