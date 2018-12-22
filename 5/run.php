#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = str_split(strrev(getInputLine()));

	function react($input, $skip = NULL) {
		$out = [];

		foreach ($input as $in) {
			if ($skip != null && ($in == $skip || (ord($skip) ^ ord($in)) == 32)) {
				continue;
			} else if (!empty($out) && (ord(end($out)) ^ ord($in)) == 32) {
				array_pop($out);
			} else {
				$out[] = $in;
			}
		}

		return $out;
	}

	$part1 = react($input);
	echo 'Part 1: ', count($part1), "\n";

	$shortest = count($part1);
	for ($unit = 65; $unit < 91; $unit++) {
		$result = react($part1, chr($unit));
		if (count($result) < $shortest) { $shortest = count($result); }
	}

	echo 'Part 2: ', $shortest, "\n";
