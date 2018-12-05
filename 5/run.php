#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	function react($input) {
		$ll = str_split($input);
		$madeChange = false;
		do {
			$madeChange = false;

			for ($i = 0; $i < count($ll)-1; $i++) {
				if ($ll[$i] != $ll[$i + 1] && strtolower($ll[$i]) == strtolower($ll[$i + 1])) {
					unset($ll[$i]);
					unset($ll[$i + 1]);
					$i++;
					$madeChange = true;
				}
			}

			$ll = array_values($ll);
		} while ($madeChange);

		return $ll;
	}

	$part1 = react($input);
	echo 'Part 1: ', count($part1), "\n";

	// Start smaller.
	$input = implode('', $part1);
	$shortest = -1;
	foreach (array_keys(count_chars(strtolower($input), 1)) as $unit) {
		$newInput = str_replace(chr($unit - 32), '', $input);
		$newInput = str_replace(chr($unit), '', $newInput);

		echo chr($unit), ' => ';
		$result = react($newInput);
		echo count($result), "\n";

		if (count($result) < $shortest || $shortest == -1) { $shortest = count($result); }
	}

	echo 'Part 2: ', $shortest, "\n";
