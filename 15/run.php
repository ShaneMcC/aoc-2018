#!/usr/bin/php
<?php
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

		public function hp() {
			return max(0, $this->hp);
		}

		public function ap() {
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
			// If we're dead we can't do anything.
			if (!$this->isAlive()) { return FALSE; }

			// If we have no targets, we can't do anything.
			if (empty($this->getAllTargets())) { return FALSE; }

			$adjacent = $this->getAdjacentTargets();

			// If we're not next to a target, we can move.
			if (empty($adjacent)) {
				// Find all other targets.
				$targets = $this->getAllTargets();

				// Get costs everywhere.
				$costs = $this->getCosts(true);

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
					[$c, $t] = $lowestCosts[0];
					$moveTo = $c['path'][0];
					$this->setLoc($moveTo);

					// Get new adjacent targets.
					$adjacent = $this->getAdjacentTargets();
				}
			}


			if (!empty($this->getAdjacentTargets())) {
				// Find the adjacent target with the least HP.
				$validTargets = [];
				$lowestHP = PHP_INT_MAX;
				foreach ($adjacent as $target) {
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
					$this->attack($validTargets[0]);
				}
			}

			return TRUE;
		}

		public function __toString() {
			$result = '';
			$result .= $this->type() == 'G' ? "\033[1;31m" : "\033[0;32m";
			$result .= $this->type();
			$result .= "\033[0m";
			// $result .= '[' . $this->id() . ']';
			$result .= '(' . $this->hp() . ')';
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

	function resetGame($elfAP = 3) {
		global $input, $grid, $maxX;

		Unit::resetUnits();

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

	function draw($costs = []) {
		global $grid, $characters;

		for ($y = 0; $y < count($grid); $y++) {
			$lineCharacters = [];

			for ($x = 0; $x < count($grid[$y]); $x++) {
				$unit = Unit::findAt($x, $y);

				if ($unit !== NULL) {
					echo $unit->type() == 'G' ? "\033[1;31m" : "\033[0;32m";
					echo $unit->type();
					echo "\033[0m";
					$lineCharacters[] = $unit;
				} else {
					$bit = $grid[$y][$x];
					$bit = $bit == '#' ? '█' : ' ';
					echo isset($costs[$y][$x]) ? $costs[$y][$x]['cost'] : $bit ;
				}
			}

			echo '    ', implode(' ', $lineCharacters), "\n";
		}

		echo "\n\n\n";
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
		foreach (Unit::getUnits() as $unit) {
			if ($unit->isAlive()) {
				if (!$unit->takeTurn()) { return FALSE; }
			}
		}

		return TRUE;
	}


	function doGame($elfAP = 3, $breakOnDeath = false) {
		resetGame($elfAP);

		if (isDebug()) {
			echo 'Initial: ', "\n";
			draw();
		}

		$continue = true;
		$rounds = 1;
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
					echo "\n", 'After ', $rounds, ' Rounds with ', $elfAP, ' AP:', "\n";
					draw();
				}
				$rounds++;
			} else {
				if (isDebug()) { echo "\n", 'Combat ended before ', $rounds, ' completed.', "\n"; }
				$rounds--;
			}
		}

		$sum = 0;
		foreach (Unit::getUnits() as $unit) {
			$sum += $unit->hp();
		}

		return [$sum, $rounds];
	}

	$part1 = doGame(3, false);

	echo 'Part 1: ', ($part1[0] * $part1[1]), ' (', $part1[0], ' x ', $part1[1], ')', "\n";

	$ap = 4;
	while (true) {
		$part2 = doGame($ap, true);
		if ($part2 === FALSE) {
			$ap++;
		} else {
			break;
		}
	}

	// Show this again because the output will have hidden it.
	if (isDebug()) { echo 'Part 1: ', ($part1[0] * $part1[1]), ' (', $part1[0], ' x ', $part1[1], ')', "\n"; }
	echo 'Part 2: ', ($part2[0] * $part2[1]), ' (', $part2[0], ' x ', $part2[1], ' with ' , $ap, ' AP)', "\n";
