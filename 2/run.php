#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$boxes = getInputLines();

	$hasTwo = $hasThree = 0;
	foreach ($boxes as $box) {
		$letters = array_count_values(count_chars($box, 1));
		$hasTwo += array_key_exists(2, $letters);
		$hasThree += array_key_exists(3, $letters);
	}
	$part1 = $hasThree * $hasTwo;
	echo 'Part 1: ', $part1, "\n";

	function getSame($box1, $box2) {
		$pos = -1;
		for ($i = 0; $i < strlen($box1); $i++) {
			if ($box1{$i} != $box2{$i}) {
				if ($pos > 0) { return FALSE; }
				$pos = $i;
			}
		}

		if ($pos === -1) { return FALSE; }
		return substr($box1, 0, $pos) . substr($box1, $pos + 1);
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
