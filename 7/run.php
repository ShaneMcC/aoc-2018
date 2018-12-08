#!/usr/bin/php
<?php
	$__CLI['long'] = ['custom', 'workers:', 'step:', 'multiplier:', 'noskip'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --custom             Enable custom mode, to run the input with the given settings';
	$__CLI['extrahelp'][] = '      --workers <#>        Run the input with this many workers (Custom Mode)';
	$__CLI['extrahelp'][] = '      --step <#>           Per-Step added time (Custom Mode)';
	$__CLI['extrahelp'][] = '      --multiplier <#>     Per-Step time multiplier (Custom Mode)';
	$__CLI['extrahelp'][] = '      --noskip             Don\'t skip seconds.';

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
		global $steps, $__CLIOPTS;

		$order = [];
		$pendingSteps = $steps;
		$availableSteps = [];

		$workers = [];
		for ($i = 0; $i < $workerCount; $i++) { $workers[$i] = ['step' => '', 'remaining' => 0]; }

		if (isDebug()) {
			echo 'Second ';
			foreach ($workers as $id => $w) { echo sprintf('  Worker %-2s', $id + 1); }
			echo '  Done';
			echo "\n";
		}

		$busy = 0;
		for ($time = 0; true; $time++) {
			// Step all workers.
			$busy = 0;
			$minRemaining = PHP_INT_MAX;
			foreach ($workers as $id => $w) {
				if (empty($w['step'])) { continue; }

				$workers[$id]['remaining']--;
				if ($workers[$id]['remaining'] <= 0) {
					$order[] = $w['step'];
					$workers[$id]['step'] = '';
				} else {
					$busy++;
					$minRemaining = min($minRemaining, $workers[$id]['remaining']);
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

				// Allocate to free workers
				if (!empty($availableSteps)) {
					foreach ($workers as $id => $w) {
						if (empty($w['step']) && !empty($availableSteps)) {
							$s = array_shift($availableSteps);
							$workers[$id]['step'] = $s;
							$workers[$id]['remaining'] = $perStep + ($multiplier * (ord($s) - 64));
							$minRemaining = min($minRemaining, $workers[$id]['remaining']);
							$busy++;
						}
					}
				}
			}

			if (isDebug()) {
				echo sprintf('%4s   ', $time);
				foreach ($workers as $id => $w) {
					echo sprintf('     %-2s    ', (!empty($w['step']) ? $w['step'] : '.'));
				}
				echo '  ', implode('', $order);
				echo "\n";
			}

			if (empty($availableSteps) && $busy == 0 && empty($pendingSteps)) {
				break;
			}

			// All possible workers are busy, advance as far as possible.
			if ($minRemaining > 1 && ($busy == $workerCount || empty($availableSteps)) && !isset($__CLIOPTS['noskip'])) {
				// Account for the fact the first thing we do in the loop is
				// do a second of work.
				$minRemaining--;
				$time += $minRemaining;

				if (isDebug()) { echo ' ... Skipping ', $minRemaining, ' ...', "\n"; }

				foreach ($workers as $id => $w) {
					$workers[$id]['remaining'] -= $minRemaining;
				}
			}
		}

		return [$order, $time];
	}


	if (isset($__CLIOPTS['custom'])) {
		$workers = isset($__CLIOPTS['workers']) ? $__CLIOPTS['workers'] : 5;
		$perStep = isset($__CLIOPTS['step']) ? $__CLIOPTS['step'] : 60;
		$multiplier = isset($__CLIOPTS['multiplier']) ? $__CLIOPTS['multiplier'] : 1;

		echo 'Running in custom mode.', "\n";
		echo '          Workers: ', $workers, "\n";
		echo '    Per-Step Time: ', $perStep, "\n";
		echo '       Multiplier: ', $multiplier, "\n";
		echo "\n";

		list($order, $time) = getSteps($workers, $perStep, $multiplier);

		echo 'Order: ', implode('', $order), "\n";
		echo ' Time: ', $time, "\n";
	} else {
		$part1 = getSteps(1, 0, 0);
		echo 'Part 1: ', implode('', $part1[0]), "\n";

		$part2 = isTest() ? getSteps(2, 0, 1) : getSteps(5, 60, 1);
		echo 'Part 2: ', $part2[1], "\n";
	}
