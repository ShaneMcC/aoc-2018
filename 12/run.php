#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$initial = '';
	$changes = array();

	foreach ($input as $details) {
		if (preg_match('#initial state: (.*)#SADi', $details, $m)) {
			$initial = $m[1];
			$initial = str_split($initial);
		} elseif (preg_match('#(.*) => (.*)#SADi', $details, $m)) {
			$changes[$m[1]] = $m[2];
		}
	}


	function doGenerations($count) {
		global $initial, $changes;

		$offset = 0;
		$state = $initial;
		$stable = 0;

		for ($gen = 0; $gen < $count; $gen++) {
			$newState = [];

			$firstAlive = array_search('#', $state);
			$lastAlive = ($firstAlive > 0) ? array_search('#', array_reverse($state)) : 0;

			// If all the plants died, exit.
			if ($firstAlive === FALSE) {
				return 0;
			}

			// Add some new pots to the start for growth space if the first
			// alive plant is too close.
			if ($firstAlive < 5) {
				for ($i = 0; $i < 5; $i++) { array_unshift($state, '.'); $offset--; }
			} else if ($firstAlive > 8) {
				// If they are too far away, bring them closer.
				$state = array_slice($state, $firstAlive - 3);
				$offset += $firstAlive - 3;
			}
			// If needed, add some more pots to the end for growth space.
			if ($lastAlive < 5) {
				for ($i = 0; $i < 5; $i++) { $state[] = '.'; }
			}

			if (isDebug()) {
				echo sprintf('%2s', $gen), ' ', implode('', $state), "\n";
			}

			for ($p = 0; $p < count($state); $p++) {
				$test = $state[$p - 2] ?? '.';
				$test .= $state[$p - 1] ?? '.';
				$test .= $state[$p];
				$test .= $state[$p + 1] ?? '.';
				$test .= $state[$p + 2] ?? '.';

				$newState[$p] = isset($changes[$test]) ? $changes[$test] : '.';
			}

			if (trim(implode('', $newState), '.') == trim(implode('', $state), '.')) {
				if (isDebug()) { echo 'Stable at: ', $gen, "\n"; }
				$offset += $count - $gen;
				break;
			}

			$state = $newState;
		}

		$res = 0;
		for ($c = 0; $c < count($state); $c++) {
			if ($state[$c] == '#') {
				$res += $c + $offset;
			}
		}

		return $res;
	}

	echo 'Part 1: ', (isDebug() ? "\n" : ''), doGenerations(20), "\n";
	if (!isTest()) {
		echo 'Part 2: ', (isDebug() ? "\n" : ''), doGenerations(50000000000), "\n";
	}
