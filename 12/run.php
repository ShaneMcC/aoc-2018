#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$state = '';
	$changes = array();

	foreach ($input as $details) {
		if (preg_match('#initial state: (.*)#SADi', $details, $m)) {
			$state = '...' . $m[1] . '............................';
			$state = str_split($state);
		} elseif (preg_match('#(.*) => (.*)#SADi', $details, $m)) {
			$changes[$m[1]] = $m[2];
		}
	}


	echo '0 ', implode($state), "\n";
	for ($i = 1; $i <= 20; $i++) {
		$newState = $state;
		for ($p = 0; $p < count($state); $p++) {
			$test = isset($state[$p - 2]) ? $state[$p - 2] : '.';
			$test .= isset($state[$p - 1]) ? $state[$p - 1] : '.';
			$test .= isset($state[$p]) ? $state[$p] : '.';
			$test .= isset($state[$p + 1]) ? $state[$p + 1] : '.';
			$test .= isset($state[$p + 2]) ? $state[$p + 2] : '.';

			$newState[$p] = isset($changes[$test]) ? $changes[$test] : '.';
		}

		$state = $newState;
		echo $i, ' ', implode('', $state), "\n";
	}

	$res = 0;
	for ($c = 0; $c < count($state); $c++) {
		if ($state[$c] == '#') {
			$res += $c - 3;
		}
	}

	echo 'Part 1: ', $res, "\n";
