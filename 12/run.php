#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$initial = '';
	$changes = array();

	foreach ($input as $details) {
		if (preg_match('#initial state: (.*)#SADi', $details, $m)) {
			$initial = '.....' . $m[1] . '.....';
			$initial = str_split($initial);
		} elseif (preg_match('#(.*) => (.*)#SADi', $details, $m)) {
			$changes[$m[1]] = $m[2];
		}
	}


	function doGenerations($count) {
		global $initial, $changes;

		$minus = 5;
		$offset = 0;

		$state = $initial;

		$stable = 0;

		if (isDebug()) { echo '0 ', implode($state), "\n"; }
		for ($i = 1; $i <= $count; $i++) {
			$newState = [];
			for ($p = 0; $p < count($state); $p++) {
				$test = isset($state[$p - 2]) ? $state[$p - 2] : '.';
				$test .= isset($state[$p - 1]) ? $state[$p - 1] : '.';
				$test .= isset($state[$p]) ? $state[$p] : '.';
				$test .= isset($state[$p + 1]) ? $state[$p + 1] : '.';
				$test .= isset($state[$p + 2]) ? $state[$p + 2] : '.';

				$newState[$p] = isset($changes[$test]) ? $changes[$test] : '.';
			}

			$newState[] = '.';

			$firstAlive = array_search('#', $newState);
			if ($firstAlive > 5) {
				$newState = array_slice($newState, $firstAlive - 5);
				$offset += $firstAlive - 5;
			}

			if (implode('', $newState) == implode('', $state)) {
				$stable++;
			}
			if ($stable > 2) {
				if (isDebug()) { echo 'Stable at: ', $i, "\n"; }
				$offset += $count - $i;
				break;
			}

			$state = $newState;
			if (isDebug()) { echo $i, ' ', implode('', $state), "\n"; }
		}

		$res = 0;
		for ($c = 0; $c < count($state); $c++) {
			if ($state[$c] == '#') {
				$res += $c - $minus + $offset;
			}
		}

		return $res;
	}

	echo 'Part 1: ', (isDebug() ? "\n" : ''), doGenerations(20), "\n";
	echo 'Part 2: ', (isDebug() ? "\n" : ''), doGenerations(50000000000), "\n";
