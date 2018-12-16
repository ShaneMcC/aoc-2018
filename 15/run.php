#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/pathfinder.php');
	$input = getInputLines();

	class Unit {
		private $id;
		private $type = 'U';
		private $ap = 3;
		private $hp = 200;

		private $x = 0;
		private $y = 0;

		private static $units;
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

		public function getPaths($target, $maxDistance = PHP_INT_MAX) {
			global $grid;

			$paths = [];
			$shortest = $maxDistance;

			foreach ($target->getAdjacentSpaces() as $t) {
				foreach ($this->getAdjacentSpaces() as $s) {
					$m = (new Day15PathFinder($grid, $s, $t))->solveMaze($shortest);

					if ($m[0] !== FALSE) {
						if ($m[0]['steps'] < $shortest) {
							$shortest = $m[0]['steps'];
							$paths = [];
						}

						if ($m[0]['steps'] == $shortest) {
							$paths[] = $s;
						}
					}
				}
			}

			return [$shortest, $paths];
		}


		public function takeTurn() {
			// If we're dead we can't do anything.
			if (!$this->isAlive()) { return FALSE; }

			// If we have no targets, we can't do anything.
			if (empty($this->getAllTargets())) { return FALSE; }

			// echo 'Unit: ', $this, "\n";

			$adjacent = $this->getAdjacentTargets();

			// If we're not next to a target, we can move.
			if (empty($adjacent)) {
				// Find all other targets.
				$targets = $this->getAllTargets();

				// Get paths to the targets.
				$shortest = PHP_INT_MAX;
				$possiblePaths = [];
				foreach ($targets as $target) {
					list($distance, $paths) = $this->getPaths($target, $shortest);

					// If we found a valid path, then consider it.
					if (!empty($paths)) {
						if ($distance < $shortest) {
							$possiblePaths = [];
							$shortest = $distance;
						}

						if ($distance == $shortest) {
							foreach ($paths as $path) {
								$possiblePaths[] = $path;
							}
						}
					}
				}

				// If we have a possible path, move to it.
				if (!empty($possiblePaths)) {
					$this->setLoc($possiblePaths[0]);

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
			return $this->type() . '[' . $this->id() . '](' . $this->hp() . ')';
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

		public static function notType($type) {
			$result = [];
			foreach (self::$units as $u) {
				if ($u->isAlive() && $u->type() != $type) {
					$result[] = $u;
				}
			}
			return $result;
		}

		public static function getUnits() {
			usort(self::$units, [__CLASS__, 'sort']);

			return self::$units;
		}
	}

	class Day15PathFinder extends PathFinder {
		public function __construct($grid, $start, $end) {
			parent::__construct($grid, $start, $end);
			$this->setHook('isAccessible', function($state, $x, $y) { return isEmpty($x, $y); });
			$this->setHook('getPoints', function($state) { list($curX, $curY) = $state['current']; return getSurrounding($curX, $curY); });
		}
	}




	$grid = [];
	$maxX = $x = $y = 0;
	foreach ($input as $in) {
		$line = [];
		$x = 0;
		foreach (str_split($in) as $bit) {
			$c = [];
			if ($bit == 'E' || $bit == 'G' ) {
				(new Unit($bit))->setLoc([$x, $y]);
				$bit = '.';
			}

			$line[] = $bit;
			$x++;
			$maxX = max($maxX, $x);
		}
		$grid[] = $line;
		$y++;
	}

	function draw() {
		global $grid, $characters;

		for ($y = 0; $y < count($grid); $y++) {
			$lineCharacters = [];

			for ($x = 0; $x < count($grid[$y]); $x++) {
				$unit = Unit::findAt($x, $y);

				if ($unit !== NULL) {
					echo $unit->type();
					$lineCharacters[] = $unit;
				} else {
					echo $grid[$y][$x];
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


	echo 'Initial: ', "\n";
	draw();

	$continue = true;
	$rounds = 1;
	while ($continue) {
		$continue = doRound();
		if ($continue) {
			echo "\n", 'After ', $rounds, ' Rounds:', "\n";
			draw();
			$rounds++;
		} else {
			echo "\n", 'Combat ended before ', $rounds, ' completed.', "\n";
			$rounds--;
		}
	}


	$sum = 0;
	foreach (Unit::getUnits() as $unit) {
		$sum += $unit->hp();
	}

	echo 'Part 1: ', ($sum * ($rounds)), "\n";
