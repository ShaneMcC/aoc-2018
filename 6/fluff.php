<?php

	function draw($areaSize = null) {
		global $grid, $__CLIOPTS, $coords;

		$colours = [];
		$canColour = (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;
		$useSymbols = isset($__CLIOPTS['symbols']);

		$reset = '';
		if ($canColour) {
			$reset = "\033[0m";
			$colours[] = "\033[1;33m";
			$colours[] = "\033[0;32m";
			$colours[] = "\033[1;37m";
			$colours[] = "\033[1;31m";
			$colours[] = "\033[1;34m";
			$colours[] = "\033[1;35m";
			$colours[] = "\033[1;36m";
		}

		$minY = $minX = PHP_INT_MAX;
		foreach ($grid as $y => $row) {
			$minY = min($y, $minY);
			foreach ($row as $x => $item) {
				$minX = min($x, $minX);

				$marker = is_int($item) ? ($canColour && !$useSymbols ? '#' : chr(33 + $item)) : ' ';

				if (is_int($item) && $coords[$item]['x'] == $x && $coords[$item]['y'] == $y) {
					$marker = 'X';

					if ($canColour) { $marker = "\033[7m" . $marker; }
				}

				echo sprintf('%s', is_int($item) ? ($canColour ? $colours[$item % count($colours)] . $marker . $reset : $marker) : $marker);
			}
			echo "\n";
		}

		echo "\n", '(Top-Right is: ', $minX, ',', $minY, ')', "\n";

		if ($areaSize != null) {
			asort($areaSize);
			echo "\n\n";
			foreach ($areaSize as $item => $size) {
				$marker = is_int($item) ? ($canColour && !$useSymbols ? '#' : chr(33 + $item)) : ' ';
				$marker = sprintf('%s', is_int($item) ? ($canColour ? $colours[$item % count($colours)] . $marker . $reset : $marker) : $marker);

				echo 'Area ', sprintf('%2s', $item), ' (', $marker, ') is: ', ($size == -1 ? 'Infinite' : $size), ' (Around point: ', $coords[$item]['x'], ',', $coords[$item]['y'], ')', "\n";
			}
		}
	}

	$__CLI['long'] = ['symbols'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --symbols            Use symbols for areas not #';
