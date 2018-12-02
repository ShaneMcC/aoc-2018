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


	function getSame($box1, $box2, $differenceLimit = 1) {
		$same = '';
		$differenceCount = 0;
		for ($i = 0; $i < strlen($box1); $i++) {
			if ($box1{$i} === $box2{$i}) {
				$same .= $box1{$i};
			} else {
				$differenceCount++;
				if ($differenceCount > $differenceLimit) { return FALSE; }
			}
		}

		if ($differenceCount === 0) { return FALSE; }

		return $same;
	}

	for ($i = 0; $i < count($boxes); $i++) {
		for ($j = $i; $j < count($boxes); $j++) {
			$result = getSame($boxes[$i], $boxes[$j], 1);

			if ($result !== FALSE) {
				echo 'Part 2: ', $result, "\n";
			}
		}
	}


