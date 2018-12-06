<?php

	function draw() {
		global $grid;

		$colours = [];
		$canColour = (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

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

		foreach ($grid as $row) {
			foreach ($row as $item) {
				echo sprintf('%s', is_int($item) ? ($canColour ? $colours[$item % count($colours)] . '#' . $reset : chr(33 + $item)) : ' ');
			}
			echo "\n";
		}
	}
