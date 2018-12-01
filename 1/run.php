#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$entries = array();
	foreach ($input as $details) {
		preg_match('#([+-])(.*)#SADi', $details, $m);
		list($all, $dir, $amount) = $m;
		$entries[] = [$dir, $amount];
	}

	$val = 0;
	foreach ($entries as $e) {
		switch ($e[0]) {
			case "+":
				$val += $e[1];
				break;
			case "-":
				$val -= $e[1];
				break;
		}
	}

	echo 'Part 1: ', $val, "\n";

	$val = 0;
	$known = [];
	while (true) {
		foreach ($entries as $e) {
			switch ($e[0]) {
				case "+":
					$val += $e[1];
					break;
				case "-":
					$val -= $e[1];
					break;
			}

			if (array_key_exists($val, $known)) { break 2; }
			$known[$val] = true;
		}
	}
	echo 'Part 2: ', $val, "\n";
