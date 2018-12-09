#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	preg_match('#([0-9]+) players; last marble is worth ([0-9]+) points#SADi', $input, $m);
	list($all, $players, $lastMarble) = $m;

	$marbles = [0 => ['prev' => 0, 'next' => 0]];
	$elves = [];
	for ($i = 0; $i < $players; $i++) { $elves[$i] = 0; }
	$current = 0;
	$currentElf = 0;

	function placeMarble($id) {
		global $marbles, $current, $elves, $currentElf;

		if ($id % 23 == 0) {
			$elves[$currentElf] += $id;

			// Get the 7th-previous marble.
			$prev = $current;
			for ($i = 0; $i < 7; $i++) {
				$prev = $marbles[$prev]['prev'];
			}
			// Remove it.

			$myPrev = $marbles[$prev]['prev'];
			$myNext = $marbles[$prev]['next'];

			$marbles[$myPrev]['next'] = $myNext;
			$marbles[$myNext]['prev'] = $myPrev;

			$elves[$currentElf] += $prev;
			$current = $myNext;

		} else {
			$nextBefore = $marbles[$current]['next'];
			$nextAfter = $marbles[$nextBefore]['next'];

			$marbles[$id] = ['prev' => $nextBefore, 'next' => $nextAfter];
			$marbles[$nextBefore]['next'] = $id;
			$marbles[$nextAfter]['prev'] = $id;
			$current = $id;
		}
	}

	function displayMarbles() {
		global $marbles, $current;

		$id = 0;
		do {
			echo ' ';
			echo $current == $id ? '(' : ' ';
			echo $id;
			echo $current == $id ? ')' : ' ';

			$id = $marbles[$id]['next'];
		} while ($id != 0);
	}

	if (isDebug()) {
		echo '[-]';
		displayMarbles();
		echo "\n";
	}

	$nextMarble = 1;
	while (true) {
		placeMarble($nextMarble++);

		if (isDebug()) {
			echo '[', $currentElf, ']';
			displayMarbles();
			echo "\n";
		}

		if ($nextMarble >= $lastMarble) { break; }

		$currentElf = ($currentElf + 1) % count($elves);
	}

	echo 'Part 1: ', max($elves), "\n";
