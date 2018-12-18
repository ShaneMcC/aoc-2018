#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$grid = [];
	$maxY = $maxX = 0;
	foreach ($input as $in) {
		$row = str_split($in);
		$grid[] = $row;
		$maxY++;
		$maxX = max(count($row), $maxX);
	}

	function draw($grid) {
		foreach ($grid as $row) {
			echo implode('', $row);
			echo "\n";
		}
		echo "\n";
	}

	function tick($grid) {
		$newGrid = [];

		foreach ($grid as $y => $row) {
			foreach ($row as $x => $acre) {
				$newbit = $bit = $grid[$y][$x];

				$openAround = 0;
				$treeAround = 0;
				$lumberAround = 0;
				foreach (yieldXY($x - 1, $y - 1, $x + 1, $y + 1) as $x2 => $y2) {
					if ($x2 == $x && $y2 == $y) { continue; }
					$around = isset($grid[$y2][$x2]) ? $grid[$y2][$x2] : '?';

					if ($around == '.') { $openAround++; }
					else if ($around == '|') { $treeAround++; }
					else if ($around == '#') { $lumberAround++; }
				}

				if ($bit == '.' && $treeAround >= 3) { $newbit = '|'; }
				else if ($bit == '|' && $lumberAround >= 3) { $newbit = '#'; }
				else if ($bit == '#' && !($lumberAround >= 1 && $treeAround >= 1)) { $newbit = '.'; }

				// echo $x, ',', $y, ' ', $bit, ' + [.', $openAround, ' |', $treeAround, ' #', $lumberAround, '] => ', $newbit, "\n";

				$newGrid[$y][$x] = $newbit;
			}
		}

		return $newGrid;
	}

	function run($grid, $minutes) {

		if (isDebug()) { draw($grid); }
		for ($minute = 1; $minute <= $minutes; $minute++) {
			$grid = tick($grid);

			if (isDebug()) {
				echo 'After ', $minute, ' minute', ($minute != 1 ? 's' : ''), ':', "\n";
				draw($grid);
			}
		}

		$wood = $lumber = 0;
		foreach ($grid as $row) {
			foreach ($row as $acre) {
				if ($acre == '|') { $wood++; }
				else if ($acre == '#') { $lumber++; }
			}
		}

		return [$wood, $lumber];
	}

	list($wood, $lumber) = run($grid, 10);
	$part1 = $wood * $lumber;
	echo 'Part 1: ', $wood, 'x', $lumber, ' = ', $part1, "\n";
