#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$ll = str_split($input);

	$madeChange = false;
	do {
		$madeChange = false;

		for ($i = 0; $i < count($ll)-1; $i++) {
			if ($ll[$i] != $ll[$i + 1] && strtolower($ll[$i]) == strtolower($ll[$i + 1])) {
				$ll[$i] = '';
				$ll[$i + 1] = '';
				$madeChange = true;
			}
		}

		$ll = str_split(implode('', $ll));
	} while ($madeChange);

	echo 'Part 1: ', count($ll), "\n";
