#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$actions = [];

	foreach ($input as $details) {
		preg_match('#\[[0-9]+-[0-9]+-[0-9]+ [0-9]+:([0-9]+)\] (.*)#SADi', $details, $m);

		list($all, $minute, $activity) = $m;

		$actions[] = ['minute' => $minute, 'activity' => $activity];
	}

	sort($actions);

	$sleepCount = [];
	$sleepMinutes = [];

	$sleepTime = $wakeTime = $currentGuard = 0;

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

			for ($i = $sleepTime; $i < $wakeTime; $i++) {
				if (!isset($sleepMinutes[$currentGuard][$i])) { $sleepMinutes[$currentGuard][$i] = 0; }
				$sleepMinutes[$currentGuard][$i]++;
			}
		}
	}
	asort($sleepCount);

	// Sort sleeping minutes
	foreach (array_keys($sleepMinutes) as $guard) { asort($sleepMinutes[$guard]); }

	// Most Asleep Guard:
	$mostAsleepGuard = array_keys($sleepCount);
	$mostAsleepGuard = array_pop($mostAsleepGuard);

	// Most Asleep Minute
	$mostAsleepMinute = array_keys($sleepMinutes[$mostAsleepGuard]);
	$mostAsleepMinute = array_pop($mostAsleepMinute);

	echo 'Part 1: ', ($mostAsleepMinute * $mostAsleepGuard), "\n";

	$part2Guard = $part2Minute = $part2MinuteCount = 0;
	foreach ($sleepMinutes as $guard => $minutes) {
		if (empty($minutes)) { continue; } // Best Guard.

		$highestMinute = array_keys($minutes);
		$highestMinute = array_pop($highestMinute);

		if ($minutes[$highestMinute] > $part2MinuteCount) {
			$part2MinuteCount = $minutes[$highestMinute];
			$part2Minute = $highestMinute;
			$part2Guard = $guard;
		}
	}

	echo 'Part 2: ', ($part2Minute * $part2Guard), "\n";
