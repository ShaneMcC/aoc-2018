#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../19/Day19VM.php');

	$input = getInputLines();
	$ip = explode(' ', array_shift($input))[1];

	$prog = Day19VM::parseInstrLines($input);

	$magic = [];
	$magic[] = (int)$prog[6][1][1];
	$magic[] = (int)$prog[7][1][0];
	$magic[] = (int)$prog[8][1][1];
	$magic[] = (int)$prog[10][1][1];
	$magic[] = (int)$prog[11][1][1];
	$magic[] = (int)$prog[12][1][1];
	$magic[] = (int)$prog[13][1][0];

	function emulate($magic) {
		$part1 = 0;
		$part2 = 0;
		$seen = [];

		$c = 0;

		while (true) {
			$a = $c | $magic[0];
			$c = $magic[1];

			while (true) {
				$c = ((($c + ($a & $magic[2])) & $magic[3]) * $magic[4]) & $magic[5];

				if ($magic[6] > $a) {
					if ($part1 == 0) {
						$part1 = $c;
					}

					if (in_array($c, $seen)) {
						break 2;
					} else {
						$seen[] = $c;
						$part2 = $c;
						break;
					}
				} else {
					$a = floor($a / $magic[6]);
				}
			}
		}

	    return [$part1, $part2];

	}

	list($part1, $part2) = emulate($magic);

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
