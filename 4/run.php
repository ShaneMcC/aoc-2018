#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	sort($input);

	$sleepCount = [];
	$sleepMinutes = [];

	$sleepTime = $currentGuard = 0;

	// Part 1.
	$mostAsleepMinutes = $mostAsleepGuard = 0;

	// Part 2.
	$mostAsleepPerMinute = $mostSleptMinute = $mostSleptMinuteGuard = 0;

	foreach ($input as $details) {
		preg_match('#\[[0-9]+-[0-9]+-[0-9]+ [0-9]+:([0-9]+)\] (.*)#SADi', $details, $m);

		list($all, $minute, $activity) = $m;

		if (preg_match('#Guard \#([0-9]+) begins shift#', $activity, $m)) {
			$currentGuard = $m[1];
			if (!isset($sleepMinutes[$currentGuard])) { $sleepMinutes[$currentGuard] = []; }
			if (!isset($sleepCount[$currentGuard])) { $sleepCount[$currentGuard] = 0; }

		} else if ($activity == 'falls asleep') {
			$sleepTime = $minute;

		} else if ($activity == 'wakes up') {
			$sleepCount[$currentGuard] += ($minute - $sleepTime);

			// If this guard has slept more total than our previous most-slept
			// guard, keep a note of this.
			if ($sleepCount[$currentGuard] > $mostAsleepMinutes) {
				$mostAsleepGuard = $currentGuard;
				$mostAsleepMinutes = $sleepCount[$currentGuard];
			}

			// Calculate how much time they spent asleep each minute.
			for ($min = $sleepTime; $min < $minute; $min++) {
				if (!isset($sleepMinutes[$currentGuard][$min])) { $sleepMinutes[$currentGuard][$min] = 0; }
				$sleepMinutes[$currentGuard][$min]++;

				// If this is the most-slept minute, then keep a note of this.
				if ($sleepMinutes[$currentGuard][$min] > $mostAsleepPerMinute) {
					$mostAsleepPerMinute = $sleepMinutes[$currentGuard][$min];
					$mostSleptMinute = $min;
					$mostSleptMinuteGuard = $currentGuard;
				}
			}
		}
	}

	// Most Asleep Minute for the Most Asleep Guard.
	$mostAsleepMinute = array_keys($sleepMinutes[$mostAsleepGuard], max($sleepMinutes[$mostAsleepGuard]))[0];

	echo 'Part 1: ', ($mostAsleepMinute * $mostAsleepGuard), "\n";
	echo 'Part 2: ', ($mostSleptMinute * $mostSleptMinuteGuard), "\n";
