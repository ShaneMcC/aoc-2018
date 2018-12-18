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
		$history = [];
		$dupe = -1;

		if (isDebug()) { draw($grid); }
		for ($minute = 1; $minute <= $minutes; $minute++) {
			$grid = tick($grid);

			$history[] = $grid;
			if (count($history) > 100) { array_shift($history); }
			for ($i = 1; $i < count($history); $i++) {
				if ($history[count($history) - 1 - $i] == $grid) {
					$dupe = $i;
					$loop = ($minute - $dupe);
					$diff = ($minutes - $loop) % $dupe;

					if (isDebug()) {
						echo 'Found duplicate state: ', $i, ' states ago.', "\n";
						echo 'Entered loop at: ', $loop, "\n";
						echo $minutes, ' is ', $diff, ' after loop.', "\n";
					}

					return $history[count($history) - 1 - $i + $diff];
				}
			}

			if (isDebug()) {
				echo 'After ', $minute, ' minute', ($minute != 1 ? 's' : ''), ':', "\n";
				draw($grid);
			}
		}

		return $grid;
	}

	function countGrid($grid) {
		$wood = $lumber = 0;
		foreach ($grid as $row) {
			foreach ($row as $acre) {
				if ($acre == '|') { $wood++; }
				else if ($acre == '#') { $lumber++; }
			}
		}

		return [$wood, $lumber];
	}

	list($wood, $lumber) = countGrid(run($grid, 10));
	echo 'Part 1: ', $wood, 'x', $lumber, ' = ', ($wood * $lumber), "\n";


	list($wood, $lumber) = countGrid(run($grid, 1000000000));
	echo 'Part 2: ', $wood, 'x', $lumber, ' = ', ($wood * $lumber), "\n";
