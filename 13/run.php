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
				$carts[] = ['x' => $x, 'y' => $y, 'direction' => $bit, 'lastChange' => 'r'];
				$c[] = count($carts) - 1;
				$bit = ($bit == '<' || $bit == '>') ? '-' : '|';
			}

			$line[] = ['bit' => $bit, 'carts' => $c];
			$x++;
		}
		$grid[] = $line;
		$y++;
	}

	function draw() {
		global $grid, $carts;

		for ($y = 0; $y < count($grid); $y++) {
			for ($x = 0; $x < count($grid[$y]); $x++) {
				$cartsAtLoc = $grid[$y][$x]['carts'];
				if (count($cartsAtLoc) == 1) {
					echo "\033[0;32m";
					echo $carts[$cartsAtLoc[0]]['direction'];
					echo "\033[0m";
				} else if (count($cartsAtLoc) > 1) {
					echo "\033[1;31m";
					echo 'X';
					echo "\033[0m";
				} else {
					echo $grid[$y][$x]['bit'] ?? '';
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

		for ($y = 0; $y < count($grid); $y++) {
			for ($x = 0; $x < count($grid[$y]); $x++) {
				$gridCarts = $grid[$y][$x]['carts'];
				$grid[$y][$x]['carts'] = [];

				while (($cart = array_shift($gridCarts)) !== null) {
					if (in_array($cart, $processed)) { $grid[$y][$x]['carts'][] = $cart; continue; }
					$processed[] = $cart;

					// Advance the cart.
					if ($carts[$cart]['direction'] == 'v') { $carts[$cart]['y']++; }
					else if ($carts[$cart]['direction'] == '^') { $carts[$cart]['y']--; }
					else if ($carts[$cart]['direction'] == '<') { $carts[$cart]['x']--; }
					else if ($carts[$cart]['direction'] == '>') { $carts[$cart]['x']++; }

					// Update direction if needed.
					$newGrid = $grid[$carts[$cart]['y']][$carts[$cart]['x']]['bit'];

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

					$y2 = $carts[$cart]['y'];
					$x2 = $carts[$cart]['x'];

					$grid[$y2][$x2]['carts'][] = $cart;
					if (count($grid[$y2][$x2]['carts']) > 1) {
						$crashes[] = $x2 . ',' . $y2;
					}
				}


			}
		}

		return $crashes;
	}

	if (isDebug()) { draw(); }

	for ($i = 1; true; $i++) {
		$crashes = tick();
		if (isDebug()) { draw(); }

		if (!empty($crashes)) {
			foreach ($crashes as $crash) {
				echo 'Crash at ', $i, ': ', $crash, "\n";
			}
			break;
		}
	}
