#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	class Unit {
		private static $teamid;
		private static $units;

		public $team;
		public $id;
		public $count;
		public $hp;
		public $info;
		public $attack;
		public $attackType;
		public $initiative;

		public function __construct($team, $count, $hit, $info, $attack, $attackType, $initiative) {
			$this->team = $team;
			$this->count = $count;
			$this->hit = $hit;
			$this->attack = $attack;
			$this->attackType = $attackType;
			$this->initiative = $initiative;

			$this->info = [];
			$info = explode('; ', $info);
			foreach ($info as $i) {
				if (preg_match('#([^ ]+) to (.*)#', $i, $m)) {
					$this->info[$m[1]] = explode(', ', $m[2]);
				}
			}

			// Set UnitIDs
			if (!isset(Unit::$teamid[$team])) { Unit::$teamid[$team] = 1; }
			$t = explode(' ', $team, 2);
			$this->id = $t[0] . Unit::$teamid[$team]++;
			Unit::$units[$this->id] = $this;
		}

		public function getDescription() {
			$infoString = [];
			foreach ($this->info as $type => $details) {
				$infoString[] = $type . ' to ' . implode(', ', $details);
			}
			$infoString = implode('; ', $infoString);

			return sprintf('%s: %d unit%s each with %d hit point%s %swith an attack that does %d %s damage at initiative %d (EP: %d)', $this->team, $this->count, ($this->count != 1 ? 's' : ''), $this->hit, ($this->hit != 1 ? 's' : ''), (!empty($infoString) ? '(' . $infoString . ') ' : ''), $this->attack, $this->attackType, $this->initiative, $this->getEffectivePower());
		}

		public function __toString() {
			return $this->id;
		}

		public function takeDamageFrom($unit) {
			$damage = $unit->calculateDamageTo($this);

			$this->count = max(0, $this->count - floor($damage / $this->hit));
		}

		public function calculateDamageTo($unit) {
			if (isset($unit->info['immune']) && in_array($this->attackType, $unit->info['immune'])) {
				return 0;
			} else if (isset($unit->info['weak']) && in_array($this->attackType, $unit->info['weak'])) {
				return $this->getEffectivePower() * 2;
			} else {
				return $this->getEffectivePower();
			}
		}

		public function getEffectivePower() {
			return $this->count * $this->attack;
		}

		public function getTargets() {
			$targets = [];
			foreach (Unit::getUnits() as $u) {
				if ($u->team == $this->team) { continue; }

				if ($this->calculateDamageTo($u) > 0) {
					$targets[] = $u;
				}
			}

			usort($targets, function ($a, $b) {
				$dA = $this->calculateDamageTo($a);
				$dB = $this->calculateDamageTo($b);

				return ($dA != $dB) ? ($dB - $dA) : Unit::sort($a, $b);
			});

			return $targets;
		}

		public function attack($target) {
			if ($target == NULL) { return; }
			if ($this->count == 0) { return; }

			$target->takeDamageFrom($this);
		}


		public static function get($id) {
			return isset(Unit::$units[$id]) ? Unit::$units[$id] : NULL;
		}

		public static function sort($a, $b) {
			$ap = $a->getEffectivePower();
			$bp = $b->getEffectivePower();

			$ai = $a->initiative;
			$bi = $b->initiative;


			return ($ap != $bp) ? ($bp - $ap) : ($bi - $ai);
		}

		public static function getUnits($team = NULL) {
			$result = [];
			foreach (Unit::$units as $u => $unit) {
				if ($unit->count > 0 && ($team == NULL || $unit->team == $team)) {
					$result[$u] = $unit;
				}
			}
			return $result;
		}

		public static function reset() {
			Unit::$teamid = [];
			Unit::$units = [];
		}
	}

	function resetGame() {
		global $input;

		Unit::reset();

		$team = 'Unknown';
		foreach ($input as $details) {
			if (preg_match('#(.*):$#SADi', $details, $m)) {
				$team = $m[1];
			} else if (preg_match('#([0-9]+) units? each with ([0-9]+) hit points? (?:\((.*)\) )?with an attack that does ([0-9]+) (.*) damage at initiative ([0-9]+)$#SADi', $details, $m)) {
				list($all, $count, $hit, $info, $attack, $attackType, $initiative) = $m;

				new Unit($team, $count, $hit, $info, $attack, $attackType, $initiative);
			}
		}
	}

	function selectTargets() {
		$targets = [];

		foreach (['Infection', 'Immune System'] as $team) {
			$units = Unit::getUnits($team);
			usort($units, ['Unit', 'sort']);

			if (empty($units)) { return FALSE; }

			foreach ($units as $unit) {
				$targets[$unit->id] = NULL;
				$wanted = $unit->getTargets();

				if (isDebug()) {
					if (empty($wanted)) {
						echo "\t\t", 'Unit ', $unit->id, ' has no available targets.', "\n";
					} else {
						echo "\t\t", 'Unit ', $unit->id, ' valid targets: ', "\n";
						foreach ($wanted as $t) {
							echo "\t\t\t", $t, ' (Damage: ', $unit->calculateDamageTo($t), ', EP: ', $t->getEffectivePower(), ', Initiative: ', $t->initiative,')', (in_array($t->id, $targets) ? ' {Already targeted}' : ''), "\n";
						}
					}
				}

				foreach ($wanted as $t) {
					if (!in_array($t->id, $targets)) {
						$targets[$unit->id] = $t->id;
						if (isDebug()) { echo "\t\t", 'Unit ', $unit->id, ' picks target: ', $t->id, "\n\n"; }
						break;
					}
				}

				if (isDebug() && !isset($targets[$unit->id])) {
					echo "\t\t", 'Unit ', $unit->id, ' picks no target.', "\n\n";
				}
			}
		}

		uksort($targets, function ($a, $b) {
			return (Unit::get($b))->initiative - (Unit::get($a))->initiative;
		});

		return $targets;
	}

	function dealDamage($targets) {
		if ($targets == FALSE) { return FALSE; }

		foreach ($targets as $a => $d) {
			$attacker = Unit::get($a);
			$defender = Unit::get($d);

			$before = NULL;
			if (isDebug()) {
				if ($defender == null) {
					echo 'Unit ', $attacker, ' has no target.', "\n";
				} else {
					$before = $defender->count;
				}
			}

			$attacker->attack($defender);

			if (isDebug() && $before !== NULL) {
				$killed = $before - $defender->count;
				echo 'Unit ', $attacker, ' attacked ', $defender, ' for ', $attacker->calculateDamageTo($defender), ' killing ', $killed, ' of ', $before, ' ', $defender->hit, 'hp units.', "\n";
			}
		}

		return TRUE;
	}


	function doRound() {
		if (isDebug()) {
			foreach (['Immune System', 'Infection'] as $team) {
				echo "\t", $team, ':', "\n";
				$empty = true;
				foreach (Unit::getUnits($team) as $unit) {
					$empty = false;
					echo "\t\t", 'Unit ', $unit, ' contains ', $unit->count, ' units with ', $unit->hit, ' HP.', "\n";
				}
				if ($empty) {
					echo "\t\t", 'No units remain.', "\n";
				}
			}
			echo "\n";
		}

		return dealDamage(selectTargets());
	}

	function playGame() {
		for ($round = 1; true; $round++) {
			if (isDebug()) { echo 'Start Round ', $round, ':', "\n"; }
			$result = doRound($round);
			if (isDebug()) { echo "\n\n"; }

			if (!$result) { break; }
		}

		return $round;
	}

	resetGame();
	playGame();
	$part1 = 0;
	foreach (Unit::getUnits() as $u) { $part1 += $u->count; }

	echo 'Part 1: ', $part1, "\n";
