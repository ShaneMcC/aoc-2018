#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$possibleCarts = $carts = $grid = [];
	$maxX = $x = $y = 0;
	foreach ($input as $in) {
		$line = [];
		$x = 0;
		foreach (str_split($in) as $bit) {
			$c = [];
			if ($bit == '^' || $bit == 'v' || $bit == '<' || $bit == '>') {
				$possibleCarts[] = ['direction' => $bit, 'x' => $x, 'y' => $y];
				$bit = ($bit == '<' || $bit == '>') ? '-' : '|';
			}

			$line[] = ['bit' => $bit, 'crash' => false];
			$x++;
			$maxX = max($maxX, $x);
		}
		$grid[] = $line;
		$y++;
	}

	foreach ($possibleCarts as $c) {
		$cart = ['direction' => $c['direction'], 'lastChange' => 'r'];
		$carts[getCartID($c['x'], $c['y'])] = $cart;
	}

	function getXY($cartID) {
		global $maxX;

		$y = floor($cartID / $maxX);
		$x = $cartID - ($y * $maxX);

		return [$x, $y];
	}

	function getCartID($x, $y) {
		global $maxX;

		$cartID = ($y * $maxX) + $x;

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
				} else if ($grid[$y][$x]['crash']) {
					echo "\033[1;31m";
					echo 'X';
					echo "\033[0m";
					$grid[$y][$x]['crash'] = false;
				} else {
					echo $grid[$y][$x]['bit'] ?? '';
				}
			}
			echo "\n";
		}
		echo "\n\n\n\n\n\n";
		usleep(1000);
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
			$newGrid = $grid[$y][$x]['bit'];

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
				$grid[$y][$x]['crash'] = true;
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
