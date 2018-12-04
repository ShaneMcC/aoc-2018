#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$actions = [];

	foreach ($input as $details) {
		preg_match('#\[([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+)\] (.*)#SADi', $details, $m);

		list($all, $year, $month, $day, $hour, $minute, $activity) = $m;

		$actions[] = ['year' => $year, 'month' => $month, 'day' => $day, 'hour' => $hour, 'minute' => $minute, 'activity' => $activity];
	}

	usort($actions, function($a, $b) {
		$aDate = mktime($a['hour'], $a['minute'], 0, $a['month'], $a['day'], $a['year']);
		$bDate = mktime($b['hour'], $b['minute'], 0, $b['month'], $b['day'], $b['year']);

		return ($aDate < $bDate) ? -1 : 1;
	});


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

	// Most Asleep Guard:
	$mostAsleepGuard = array_keys($sleepCount);
	$mostAsleepGuard = array_pop($mostAsleepGuard);

	// Most Asleep Minute:
	asort($sleepMinutes[$mostAsleepGuard]);
	$mostAsleepMinute = array_keys($sleepMinutes[$mostAsleepGuard]);
	$mostAsleepMinute = array_pop($mostAsleepMinute);

	echo 'Part 1: ', ($mostAsleepMinute * $mostAsleepGuard), "\n";
