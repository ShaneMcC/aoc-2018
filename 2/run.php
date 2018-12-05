#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$boxes = getInputLines();

	$first = $second = [];

	$hasTwo = $hasThree = 0;
	foreach ($boxes as $box) {
		// Calculate part 1
		$letters = array_count_values(count_chars($box, 1));
		$hasTwo += array_key_exists(2, $letters);
		$hasThree += array_key_exists(3, $letters);

		// Used for part 2.
		$f = substr($box, 0, 13);
		$s = substr($box, 13);

		$first[$f][$s] = true;
		$second[$s][$f] = true;
	}
	$part1 = $hasThree * $hasTwo;
	echo 'Part 1: ', $part1, "\n";

	function getSame($box1, $box2) {
		$pos = -1;
		for ($i = 0; $i < strlen($box1); $i++) {
			if ($box1{$i} != $box2{$i}) {
				if ($pos >= 0) { return FALSE; }
				$pos = $i;
			}
		}

		if ($pos === -1) { return FALSE; }
		return substr($box1, 0, $pos) . substr($box1, $pos + 1);
	}

	foreach ($boxes as $box) {
		$myFirst = substr($box, 0, 13);
		$mySecond = substr($box, 13);

		$options = [];
		foreach (array_keys($first[$myFirst]) as $s) { $options[] = $myFirst . $s; }
		foreach (array_keys($second[$mySecond]) as $f) { $options[] = $f . $mySecond; }

		foreach ($options as $option) {
			$result = getSame($box, $option);

			if ($result !== FALSE) {
				echo 'Part 2: ', $result, "\n";
				break 2;
			}
		}
	}
