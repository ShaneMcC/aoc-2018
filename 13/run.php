#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$grid = [];
	$carts = [];
	$x = $y = 0;
	foreach ($input as $in) {
		$line = [];
		$x = 0;
		foreach (str_split($in) as $bit) {
			$c = [];
			if ($bit == '^' || $bit == 'v' || $bit == '<' || $bit == '>') {
				$cartID = getCartID($x, $y);
				$carts[$cartID] = ['direction' => $bit, 'lastChange' => 'r'];
				$bit = ($bit == '<' || $bit == '>') ? '-' : '|';
			}

			$line[] = $bit;
			$x++;
		}
		$grid[] = $line;
		$y++;
	}

	function getXY($cartID) {
		$y = floor($cartID / 1000);
		$x = $cartID - ($y * 1000);

		return [$x, $y];
	}

	function getCartID($x, $y) {
		$cartID = ($y * 1000) + $x;

		return $cartID;
	}

	function draw() {
		global $grid, $carts;

		for ($y = 0; $y < count($grid); $y++) {
			for ($x = 0; $x < count($grid[$y]); $x++) {
				$cartID = getCartID($x, $y);

				if (isset($carts[$cartID])) {
					echo "\033[0;32m";
					echo $carts[$cartID]['direction'];
					echo "\033[0m";
					/* echo "\033[1;31m";
					echo 'X';
					echo "\033[0m"; */
				} else {
					echo $grid[$y][$x] ?? '';
				}
			}
			echo "\n";
		}
		echo "\n\n\n\n\n\n";
	}

	function tick() {
		global $grid, $carts;

		$processed = [];
		$crashes = [];

		ksort($carts);

		foreach (array_keys($carts) as $cart) {
				if (!isset($carts[$cart])) { continue; }
				list($x, $y) = getXY($cart);

				// Advance the cart.
				if ($carts[$cart]['direction'] == 'v') { $y++; }
				else if ($carts[$cart]['direction'] == '^') { $y--; }
				else if ($carts[$cart]['direction'] == '<') { $x--; }
				else if ($carts[$cart]['direction'] == '>') { $x++; }

				// Update direction if needed.
				$newGrid = $grid[$y][$x];

				if ($newGrid == '/') {
					if ($carts[$cart]['direction'] == 'v') { $carts[$cart]['direction'] = '<'; }
					else if ($carts[$cart]['direction'] == '^') { $carts[$cart]['direction'] = '>'; }
					else if ($carts[$cart]['direction'] == '<') { $carts[$cart]['direction'] = 'v'; }
					else if ($carts[$cart]['direction'] == '>') { $carts[$cart]['direction'] = '^'; }
				} else if ($newGrid == '\\') {
					if ($carts[$cart]['direction'] == 'v') { $carts[$cart]['direction'] = '>'; }
					else if ($carts[$cart]['direction'] == '^') { $carts[$cart]['direction'] = '<'; }
					else if ($carts[$cart]['direction'] == '<') { $carts[$cart]['direction'] = '^'; }
					else if ($carts[$cart]['direction'] == '>') { $carts[$cart]['direction'] = 'v'; }
				} else if ($newGrid == '+') {
					if ($carts[$cart]['lastChange'] == 'r') { $change = 'l'; }
					if ($carts[$cart]['lastChange'] == 'l') { $change = 's'; }
					if ($carts[$cart]['lastChange'] == 's') { $change = 'r'; }

					$carts[$cart]['lastChange'] = $change;

					if ($change == 'l') {
						if ($carts[$cart]['direction'] == 'v') { $carts[$cart]['direction'] = '>'; }
						else if ($carts[$cart]['direction'] == '^') { $carts[$cart]['direction'] = '<'; }
						else if ($carts[$cart]['direction'] == '<') { $carts[$cart]['direction'] = 'v'; }
						else if ($carts[$cart]['direction'] == '>') { $carts[$cart]['direction'] = '^'; }
					} else if ($change == 'r') {
						if ($carts[$cart]['direction'] == 'v') { $carts[$cart]['direction'] = '<'; }
						else if ($carts[$cart]['direction'] == '^') { $carts[$cart]['direction'] = '>'; }
						else if ($carts[$cart]['direction'] == '<') { $carts[$cart]['direction'] = '^'; }
						else if ($carts[$cart]['direction'] == '>') { $carts[$cart]['direction'] = 'v'; }
					}
				}

				$newID = getCartID($x, $y);
				$cartData = $carts[$cart];
				unset($carts[$cart]);
				if (isset($carts[$newID])) {
					$crashes[] = ['x' => $x, 'y' => $y];
					unset($carts[$newID]);
				} else {
					$carts[$newID] = $cartData;
				}


		}

		return $crashes;
	}

	if (isDebug()) { draw(); }

	$part1 = $part2 = NULL;

	for ($i = 1; ($part2 === NULL); $i++) {
		$crashes = tick();
		if (isDebug()) { draw(); }

		if (!empty($crashes)) {
			foreach ($crashes as $crash) {
				if (isDebug()) { echo 'Crash at ', $i, ': ', $crash['x'], ',', $crash['y'], "\n"; }
				if ($part1 == NULL) { $part1 = $crash['x'] . ',' . $crash['y']; }
			}

			if (count($carts) == 1) {
				$c = array_keys($carts);
				$c = array_shift($c);
				list($x, $y) = getXY($c);
				$part2 = 'Last cart is at ' . $x . ',' . $y;
			} else if (empty($carts)) {
				$part2 = 'All carts destroyed.';
			}
		}
	}


	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
