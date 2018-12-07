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

	function getSteps() {
		global $steps;

		$order = [];
		$pendingSteps = $steps;

		while (!empty($pendingSteps)) {
			$availableSteps = [];
			foreach ($pendingSteps as $id => $step) {
				foreach ($step['requires'] as $b) {
					if (!in_array($b, $order)) {
						continue 2;
					}
				}

				$availableSteps[] = $id;
			}
			sort($availableSteps);

			$order[] = $availableSteps[0];
			unset($pendingSteps[$availableSteps[0]]);
		}

		return $order;
	}

	$steps = getSteps();
	echo 'Part 1: ', implode('', $steps), "\n";
