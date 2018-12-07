#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');

	$input = getInputLines();

	$steps = [];
	foreach ($input as $details) {
		preg_match('#Step ([A-Z]+) must be finished before step ([A-Z]+) can begin.#SADi', $details, $m);
		list($all, $a, $b) = $m;

		if (!isset($steps[$a])) { $steps[$a] = ['requires' => []]; }

		$steps[$b]['requires'][] = $a;
	}

	function getSteps($workerCount, $perStep, $multiplier) {
		global $steps;

		$order = [];
		$pendingSteps = $steps;
		$availableSteps = [];

		$workers = [];
		for ($i = 0; $i < $workerCount; $i++) { $workers[$i] = ['step' => '', 'remaining' => 0]; }

		$time = -1;
		$busy = 0;
		while ($busy != 0 || !empty($pendingSteps) || !empty($availableSteps)) {
			$time++;

			// Step all workers.
			$busy = 0;
			foreach ($workers as $id => &$w) {
				if (empty($w['step'])) { continue; }

				$w['remaining']--;
				if ($w['remaining'] <= 0) {
					$order[] = $w['step'];
					$w['step'] = '';
				} else {
					$busy++;
				}
			}

			if ($busy != $workerCount) {
				foreach ($pendingSteps as $id => $step) {
					foreach ($step['requires'] as $b) {
						if (!in_array($b, $order)) {
							continue 2;
						}
					}

					$availableSteps[] = $id;
					unset($pendingSteps[$id]);
				}
				sort($availableSteps);
			}

			// Allocate to free workers
			if (!empty($availableSteps)) {
				foreach ($workers as $id => &$w) {
					if (empty($w['step'])) {
						$s = array_shift($availableSteps);
						$w['step'] = $s;
						$w['remaining'] = $perStep + ($multiplier * (ord($s) - 64));
						$busy++;
					}
				}
			}

			if (isDebug()) {
				echo $time, '    ';
				foreach ($workers as $z) {
					echo (!empty($z['step']) ? $z['step'] : '.'), '    ';
				}
				echo implode('', $order);
				echo "\n";
			}
		}

		return [$order, $time];
	}

	$part1 = getSteps(1, 0, 0);
	echo 'Part 1: ', implode('', $part1[0]), "\n";

	$part2 = getSteps(5, 60, 1);
	echo 'Part 2: ', $part2[1], "\n";
