#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$boxes = getInputLines();

	$hasThree = $hasTwo = [];
	foreach ($boxes as $box) {
		$letters = array_count_values(str_split($box));

		foreach ($letters as $v) {
			if ($v == 2) { $hasTwo[$box] = true; }
			if ($v == 3) { $hasThree[$box] = true; }
		}
	}
	$part1 = count($hasThree) * count($hasTwo);
	echo 'Part 1: ', $part1, "\n";

	function getSame($box1, $box2, $differenceLimit = 1) {
		$same = '';
		$differenceCount = 0;
		for ($i = 0; $i < strlen($box1); $i++) {
			if ($box1{$i} == $box2{$i}) {
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
			$result = getSame($boxes[$i], $boxes[$j]);

			if ($result !== FALSE) {
				echo 'Part 2: ', $result, "\n";
				break 2;
			}
		}
	}


