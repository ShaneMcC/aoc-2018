#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	sort($input);
	$actions = [];
	foreach ($input as $details) {
		preg_match('#\[[0-9]+-[0-9]+-[0-9]+ [0-9]+:([0-9]+)\] (.*)#SADi', $details, $m);

		list($all, $minute, $activity) = $m;

		$actions[] = ['minute' => $minute, 'activity' => $activity];
	}

	$sleepCount = [];
	$sleepMinutes = [];

	$sleepTime = $wakeTime = $currentGuard = 0;

	// Part 1.
	$mostAsleepMinutes = $mostAsleepGuard = 0;

	// Part 2.
	$part2MinuteCount = $part2Minute = $part2Guard = 0;

	// Calculate sleep times
	foreach ($actions as $action) {
		if (preg_match('#Guard \#([0-9]+) begins shift#', $action['activity'], $m)) {
			$currentGuard = $m[1];
			if (!isset($sleepMinutes[$currentGuard])) { $sleepMinutes[$currentGuard] = []; }
			if (!isset($sleepCount[$currentGuard])) { $sleepCount[$currentGuard] = 0; }

		} else if ($action['activity'] == 'falls asleep') {
			$sleepTime = $action['minute'];
		} else if ($action['activity'] == 'wakes up') {
			$wakeTime = $action['minute'];

			$sleepCount[$currentGuard] += ($wakeTime - $sleepTime);
			if ($sleepCount[$currentGuard] > $mostAsleepMinutes) {
				$mostAsleepGuard = $currentGuard;
				$mostAsleepMinutes = $sleepCount[$currentGuard];
			}

			for ($min = $sleepTime; $min < $wakeTime; $min++) {
				if (!isset($sleepMinutes[$currentGuard][$min])) { $sleepMinutes[$currentGuard][$min] = 0; }
				$sleepMinutes[$currentGuard][$min]++;

				if ($sleepMinutes[$currentGuard][$min] > $part2MinuteCount) {
					$part2MinuteCount = $sleepMinutes[$currentGuard][$min];
					$part2Minute = $min;
					$part2Guard = $currentGuard;
				}
			}
		}
	}

	// Most Asleep Minute for the Most Asleep Guard.
	asort($sleepMinutes[$mostAsleepGuard]);
	$mostAsleepMinute = array_keys($sleepMinutes[$mostAsleepGuard]);
	$mostAsleepMinute = array_pop($mostAsleepMinute);

	echo 'Part 1: ', ($mostAsleepMinute * $mostAsleepGuard), "\n";
	echo 'Part 2: ', ($part2Minute * $part2Guard), "\n";
