#!/usr/bin/php
<?php
	$__CLI['long'] = ['id', 'part1', 'part2', 'custom', 'eap:', 'ehp:', 'gap:', 'ghp:', 'break', 'debugturn', 'turndebug', 'xy', 'nocolour', 'hashdot', 'peaceful'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --nocolour           Don\'t include colours in output';
	$__CLI['extrahelp'][] = '      --hashdot            Use # and . in cave output';
	$__CLI['extrahelp'][] = '      --id                 Include Elf IDs in debug output';
	$__CLI['extrahelp'][] = '      --xy                 Include Elf XY in debug output';
	$__CLI['extrahelp'][] = '      --part1              run part 1';
	$__CLI['extrahelp'][] = '      --part2              run part 2';
	$__CLI['extrahelp'][] = '      --debugturn          debug each individual turn';
	$__CLI['extrahelp'][] = '      --custom             run part 1 in custom mode';
	$__CLI['extrahelp'][] = '      --eap <#>            Elf AP in custom mode';
	$__CLI['extrahelp'][] = '      --ehp <#>            Elf HP in custom mode';
	$__CLI['extrahelp'][] = '      --gap <#>            Goblin AP in custom mode';
	$__CLI['extrahelp'][] = '      --ghp <#>            Goblin HP in custom mode';
	$__CLI['extrahelp'][] = '      --break              Exit if an elf dies in custom mode';
	$__CLI['extrahelp'][] = '      --peaceful           Don\'t engage in combat';

	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	class Unit {
		private $id;
		private $type = 'U';
		private $ap = 3;
		private $hp = 200;

		private $x = 0;
		private $y = 0;

		private static $units = [];
		private static $unitID = 0;

		public function __construct($type, $ap = 3, $hp = 200) {
			$this->type = $type;
			$this->ap = $ap;
			$this->hp = $hp;

			$this->id = self::$unitID++;

			self::$units[] = $this;
		}

		public function attack($target) {
			$target->hp -= $this->ap;
		}

		public function isAlive() {
			return $this->hp > 0;
		}

		public function getSortID() {
			global $maxX;
			return ($this->y * $maxX) + $this->x;
		}

		public function id() {
			return $this->id;
		}

		public function type() {
			return $this->type;
		}

		public function hp($val = null) {
			if ($val != null) { $this->hp = $val; }
			return max(0, $this->hp);
		}

		public function ap($val = null) {
			if ($val != null) { $this->ap = $val; }
			return $this->ap;
		}

		public function getLoc() {
			return [$this->x, $this->y];
		}

		public function setLoc($loc) {
			$this->x = $loc[0];
			$this->y = $loc[1];
		}

		public function getAllTargets() {
			return self::notType($this->type);
		}

		public function getCosts() {
			global $grid;

			$costs = [];

			$loc = $this->getLoc();
			$state = [[$loc, 0]];
			$costs[$loc[1]][$loc[0]] = ['cost' => 0, 'path' => []];

			while (!empty($state)) {
				list($cur, $cost) = array_shift($state);

				foreach (getSurrounding($cur[0], $cur[1]) as $s) {
					if (isEmpty($s[0], $s[1]) && !isset($costs[$s[1]][$s[0]])) {
						$state[] = [$s, ($cost + 1)];
						$old = $costs[$cur[1]][$cur[0]];
						$costs[$s[1]][$s[0]] = ['cost' => ($cost + 1), 'path' => $old['path']];
						$costs[$s[1]][$s[0]]['path'][] = $s;
					}
				}
			}

			return $costs;
		}

		public function getAdjacentTargets() {
			$targets = [];
			foreach (getSurrounding($this->x, $this->y) as $loc) {
				$t = self::findAt($loc[0], $loc[1]);
				if ($t != NULL && $t->type != $this->type) {
					$targets[] = $t;
				}
			}
			return $targets;
		}

		public function getAdjacentSpaces() {
			$spaces = [];
			foreach (getSurrounding($this->x, $this->y) as $loc) {
				if (isEmpty($loc[0], $loc[1])) {
					$spaces[] = $loc;
				}
			}

			return $spaces;
		}

		public function takeTurn() {
			global $__CLIOPTS;
			$debugTurn = isDebug() && (isset($__CLIOPTS['debugturn']) || isset($__CLIOPTS['turndebug']));

			// If we're dead we can't do anything.
			if (!$this->isAlive()) { return FALSE; }

			if ($debugTurn) { echo "\t", 'Unit ', $this->id(), ' takes a turn.', "\n"; }

			// If we have no targets, we can't do anything.
			if (empty($this->getAllTargets())) {
				if ($debugTurn) { echo "\t\t", 'Unit ', $this, ' sees no targets.', "\n"; }
				return FALSE;
			}

			$adjacent = $this->getAdjacentTargets();

			// If we're not next to a target, we can move.
			if (empty($adjacent)) {
				if ($debugTurn) { echo "\t\t", 'Unit ', $this, ' wants to move.', "\n"; }

				// Find all other targets.
				$targets = $this->getAllTargets();

				// Get costs everywhere.
				$costs = $this->getCosts();

				// Get paths to the targets.
				$lowestCosts = [];
				$lowestCost = PHP_INT_MAX;
				foreach ($targets as $target) {
					foreach ($target->getAdjacentSpaces() as $s) {
						// Is the space reachable?
						if (!isset($costs[$s[1]][$s[0]])) { continue; }

						$cost = $costs[$s[1]][$s[0]];
						if ($cost['cost'] < $lowestCost) {
							$lowestCost = $cost['cost'];
							$lowestCosts = [];
						}
						if ($cost['cost'] == $lowestCost) {
							$lowestCosts[] = [$cost, $target];
						}
					}
				}

				// If we have a possible path, move to it.
				if (!empty($lowestCosts)) {
					// Sort the costs.
					usort($lowestCosts, function ($a, $b) {
						global $maxX;

						// Firstly, sort based on the reading-order of the LAST
						// point in the path.
						list($aeX, $aeY) = $a[0]['path'][count($a[0]['path']) - 1];
						list($beX, $beY) = $b[0]['path'][count($b[0]['path']) - 1];

						$aeSort = ($aeY * $maxX) + $aeX;
						$beSort = ($beY * $maxX) + $beX;

						// If the last points are identical, then we sort by
						// the FIRST point instead.
						if ($aeSort == $beSort) {
							list($asX, $asY) = $a[0]['path'][0];
							list($bsX, $bsY) = $b[0]['path'][0];

							$asSort = ($asY * $maxX) + $asX;
							$bsSort = ($bsY * $maxX) + $bsX;

							return ($asSort < $bsSort) ? -1 : (($asSort == $bsSort) ? 0 : 1);
						} else {
							return ($aeSort < $beSort) ? -1 : 1;
						}
					});

					if ($debugTurn) {
						$pathNumber = 1;
						foreach ($lowestCosts as $p) {
							[$c, $t] = $p;
							echo "\t\t", 'Unit ', $this, ' has a ', $c['cost'], '-cost path towards ', $t, ' (Path: ', $pathNumber++, ' of ', count($lowestCosts), ')', "\n";
							$debugpath = [];
							$marker = '*';
							if (!isset($__CLIOPTS['nocolour'])) { $marker = ($this->type() == 'G' ? "\033[1;31m" : "\033[0;32m") . $marker . "\033[0m"; }
							foreach ($c['path'] as $p) { $debugpath[$p[1]][$p[0]] = ['cost' => $marker]; }
							draw($debugpath, "\t\t\t");
							echo "\n";
						}
					}

					[$c, $t] = $lowestCosts[0];
					$moveTo = $c['path'][0];

					if ($debugTurn) {
						echo "\t\t", 'Unit ', $this, ' is moving towards to ', implode(',', $moveTo), ' towards ', $t, "\n";
						$debugpath = [];
						$marker = 'x';
						if (!isset($__CLIOPTS['nocolour'])) { $marker = ($this->type() == 'G' ? "\033[1;31m" : "\033[0;32m") . $marker . "\033[0m"; }
						foreach ($c['path'] as $p) { $debugpath[$moveTo[1]][$moveTo[0]] = ['cost' => $marker]; }
						draw($debugpath, "\t\t\t");
						echo "\n";
					}

					$this->setLoc($moveTo);

					// Get new adjacent targets.
					$adjacent = $this->getAdjacentTargets();
				} else if ($debugTurn) {
					echo "\t\t", 'Unit ', $this, ' has nowhere to move to.', "\n";
				}
			}


			if (!isset($__CLIOPTS['peaceful']) && !empty($this->getAdjacentTargets())) {
				if ($debugTurn) { echo "\t\t", 'Unit ', $this, ' wants to fight.', "\n"; }

				// Find the adjacent target with the least HP.
				$validTargets = [];
				$possibleTargets = [];
				$lowestHP = PHP_INT_MAX;
				foreach ($adjacent as $target) {
					$possibleTargets[] = $target;
					if ($target->hp() < $lowestHP) {
						$lowestHP = $target->hp();
						$validTargets = [];
					}
					if ($target->hp() == $lowestHP) {
						$validTargets[] = $target;
					}
				}

				// If we have a target, attack them.
				if (isset($validTargets[0])) {
					if ($debugTurn) {
						echo "\t\t", 'Unit ', $this, ' has ', count($possibleTargets), ' possible targets.', "\n";
						foreach ($possibleTargets as $pt) {
							echo "\t\t\t", 'Possible Target: ', $pt, "\n";
							if (in_array($pt, $validTargets)) {
								echo "\t\t\t\t", 'Target is valid.', "\n";
							}
						}
						echo "\t\t", 'Unit ', $this, ' decides to fight: ', $validTargets[0], "\n";
					}
					$this->attack($validTargets[0]);
					if ($debugTurn) { echo "\t\t\t", 'Result: ', $validTargets[0], "\n"; }
				}
			}

			if ($debugTurn) { echo "\n"; }

			return TRUE;
		}

		public function __toString() {
			global $__CLIOPTS;

			$result = '';
			if (!isset($__CLIOPTS['nocolour'])) { $result .= $this->type() == 'G' ? "\033[1;31m" : "\033[0;32m"; }
			$result .= $this->type();
			if (!isset($__CLIOPTS['nocolour'])) { $result .= "\033[0m";}
			if (isset($__CLIOPTS['id'])) {
				$result .= '[' . $this->id() . ']';
			}
			if (isset($__CLIOPTS['xy'])) {
				$result .= '{' . implode(',', $this->getLoc()) . '}';
			}
			$result .= '(' . $this->hp() . ')';
			if (!$this->isAlive()) {
				$result .= 'X';
			}
			return $result;
		}

		public static function sort($a, $b) {
			return ($a->getSortID() < $b->getSortID()) ? -1 : 1;
		}

		public static function findAt($x, $y) {
			foreach (self::$units as $u) {
				if ($u->isAlive() && $u->getLoc() == [$x, $y]) {
					return $u;
				}
			}

			return NULL;
		}

		public static function get($id) {
			foreach (self::$units as $u) {
				if ($u->id() == $id) {
					return $u;
				}
			}

			return NULL;
		}


		public static function notType($type, $includeDead = false) {
			$result = [];
			foreach (self::$units as $u) {
				if (($includeDead || $u->isAlive()) && $u->type() != $type) {
					$result[] = $u;
				}
			}
			return $result;
		}

		public static function getUnits() {
			usort(self::$units, [__CLASS__, 'sort']);

			return self::$units;
		}

		public static function resetUnits() {
			self::$units = [];
			self::$unitID = 0;
		}
	}

	function resetGame($elfAP = null) {
		global $input, $grid, $maxX;

		Unit::resetUnits();

		if ($elfAP == null) { $elfAP = 3; }

		$grid = [];
		$maxX = $x = $y = 0;
		foreach ($input as $in) {
			$line = [];
			$x = 0;
			foreach (str_split($in) as $bit) {
				$c = [];
				if ($bit == 'E' || $bit == 'G' ) {
					(new Unit($bit, ($bit == 'E' ? $elfAP : 3)))->setLoc([$x, $y]);
					$bit = '.';
				}

				$line[] = $bit;
				$x++;
				$maxX = max($maxX, $x);
			}
			$grid[] = $line;
			$y++;
		}
	}

	function draw($costs = [], $prefix = '') {
		global $grid, $characters, $__CLIOPTS;

		for ($y = 0; $y < count($grid); $y++) {
			$lineCharacters = [];
			echo $prefix;
			for ($x = 0; $x < count($grid[$y]); $x++) {
				$unit = Unit::findAt($x, $y);

				if ($unit !== NULL) {
					if (!isset($__CLIOPTS['nocolour'])) { echo $unit->type() == 'G' ? "\033[1;31m" : "\033[0;32m"; }
					echo $unit->type();
					if (!isset($__CLIOPTS['nocolour'])) { echo "\033[0m"; }
					$lineCharacters[] = $unit;
				} else {
					$bit = $grid[$y][$x];
					if (!isset($__CLIOPTS['hashdot'])) { $bit = $bit == '#' ? '█' : ' '; }
					echo isset($costs[$y][$x]) ? $costs[$y][$x]['cost'] : $bit ;
				}
			}

			echo '    ', implode(' ', $lineCharacters), "\n";
		}
	}

	function isEmpty($x, $y) {
		global $grid;

		return isset($grid[$y][$x]) && $grid[$y][$x] == '.' && Unit::findAt($x, $y) == NULL;
	}

	function getSurrounding($x, $y) {
		$locations = [];

		$locations[] = [$x, $y - 1];
		$locations[] = [$x - 1, $y];
		$locations[] = [$x + 1, $y];
		$locations[] = [$x, $y + 1];

		return $locations;
	}


	function doRound() {
		global $__CLIOPTS;
		$debugTurn = isDebug() && (isset($__CLIOPTS['debugturn']) || isset($__CLIOPTS['turndebug']));

		if ($debugTurn) { echo 'Starting round.', "\n"; }

		$peaceful = isset($__CLIOPTS['peaceful']);
		$movement = false;

		foreach (Unit::getUnits() as $unit) {
			if ($unit->isAlive()) {
				if ($peaceful) { $lastLoc = $unit->getLoc(); }
				if (!$unit->takeTurn()) { return FALSE; }
				if ($peaceful) { if ($lastLoc != $unit->getLoc()) { $movement = true; } }
			}
		}

		// We stalled.
		if ($peaceful && !$movement) {
			if ($debugTurn) { echo "\t", 'Nothing useful happened this round.', "\n"; }
			return FALSE;
		}

		return TRUE;
	}


	function doGame($elfAP = null, $breakOnDeath = false, $noReset = false) {
		global $__CLIOPTS;

		if (!$noReset) {
			resetGame($elfAP);
		}

		if (isDebug()) {
			echo 'Initial: ', "\n";
			draw();
			echo "\n\n\n";
		}

		$continue = true;
		$round = 1;
		while ($continue) {
			$continue = doRound();

			if ($breakOnDeath) {
				foreach (Unit::notType('G', true) as $unit) {
					if (!$unit->isAlive()) {
						if (isDebug()) { echo "\n", 'An elf died.', "\n"; }
						return FALSE;
					}
				}
			}

			if ($continue) {
				if (isDebug()) {
					echo "\n", 'After round ', $round;
					if ($elfAP != null) { echo ' with ', $elfAP, ' AP'; }
					echo ':', "\n";
					draw();
					echo "\n\n\n";
				}
				$round++;
			} else {
				if (isDebug()) {
					$peaceful = isset($__CLIOPTS['peaceful']);
					if ($peaceful) {
						echo "\n", 'No more moves are possible.', "\n\n\n";
					} else {
						echo "\n", 'Combat ended before round ', $round, ' completed:', "\n";
						draw();
						echo "\n\n\n";
					}
				}
				$round--;
			}
		}

		$sum = 0;
		foreach (Unit::getUnits() as $unit) {
			$sum += $unit->hp();
		}

		return [$sum, $round];
	}

	if (isset($__CLIOPTS['custom'])) {
		$goblinAP = isset($__CLIOPTS['gap']) ? $__CLIOPTS['gap'] : 3;
		$goblinHit = isset($__CLIOPTS['ghp']) ? $__CLIOPTS['ghp'] : 200;
		$elfAP = isset($__CLIOPTS['eap']) ? $__CLIOPTS['eap'] : 3;
		$elfHit = isset($__CLIOPTS['ehp']) ? $__CLIOPTS['ehp'] : 200;
		$exitOnElfDeath = isset($__CLIOPTS['break']);

		echo 'Running with custom settings: ', "\n";
		echo "\t", 'Goblin AP: ', $goblinAP, "\n";
		echo "\t", 'Goblin HP: ', $goblinHit, "\n";
		echo "\t", 'Elf AP: ', $elfAP, "\n";
		echo "\t", 'Elf HP: ', $elfHit, "\n";
		echo "\n\n";

		resetGame();
		foreach (Unit::getUnits() as $u) {
			if ($u->type() == 'G') {
				$u->hp($goblinHit);
				$u->ap($goblinAP);
			} else if ($u->type() == 'E') {
				$u->hp($elfHit);
				$u->ap($elfAP);
			}
		}
		$result = doGame(null, $exitOnElfDeath, true);

		echo "\n";
		echo 'Running with custom settings: ', "\n";
		echo "\t", 'Goblin AP: ', $goblinAP, "\n";
		echo "\t", 'Goblin HP: ', $goblinHit, "\n";
		echo "\t", 'Elf AP: ', $elfAP, "\n";
		echo "\t", 'Elf HP: ', $elfHit, "\n";
		echo "\n\n";
		echo 'Result: ', ($result[0] * $result[1]), ' (', $result[0], ' x ', $result[1], ')', "\n";
		die();
	}

	$runPart1 = isset($__CLIOPTS['part1']) || (!isset($__CLIOPTS['part1']) && !isset($__CLIOPTS['part2']));
	$runPart2 = isset($__CLIOPTS['part2']) || (!isset($__CLIOPTS['part1']) && !isset($__CLIOPTS['part2']));

	if ($runPart1) {
		$part1 = doGame(null, false);
		echo 'Part 1: ', ($part1[0] * $part1[1]), ' (', $part1[0], ' x ', $part1[1], ')', "\n";
	}

	if ($runPart2) {
		$ap = 4;
		while (true) {
			$part2 = doGame($ap, true);
			if ($part2 === FALSE) {
				$ap++;
			} else {
				break;
			}
		}
	}

	// Show this again because the output will have hidden it.
	if (isDebug() && $runPart1 && $runPart2) { echo 'Part 1: ', ($part1[0] * $part1[1]), ' (', $part1[0], ' x ', $part1[1], ')', "\n"; }
	if ($runPart2) { echo 'Part 2: ', ($part2[0] * $part2[1]), ' (', $part2[0], ' x ', $part2[1], ' with ' , $ap, ' AP)', "\n"; }
