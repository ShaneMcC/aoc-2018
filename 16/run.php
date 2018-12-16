#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$samples = [];
	$test = [];

	class VM {
		private $instructions = [];
		private $reg = [0, 0, 0, 0];
		private $map = [];

		public function __construct() {
			$this->init();
		}

		private function init() {
			$this->instructions['addr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] + $vm->reg[$b]; };
			$this->instructions['addi'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] + $b; };

			$this->instructions['mulr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] * $vm->reg[$b]; };
			$this->instructions['muli'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] * $b; };

			$this->instructions['banr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] & $vm->reg[$b]; };
			$this->instructions['bani'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] & $b; };

			$this->instructions['borr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] | $vm->reg[$b]; };
			$this->instructions['bori'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a] | $b; };

			$this->instructions['setr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $vm->reg[$a]; };
			$this->instructions['seti'] = function($vm, $a, $b, $c) { $vm->reg[$c] = $a; };

			$this->instructions['gtir'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($a > $vm->reg[$b]) ? 1 : 0; };
			$this->instructions['gtri'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($vm->reg[$a] > $b) ? 1 : 0; };
			$this->instructions['gtrr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($vm->reg[$a] > $vm->reg[$b]) ? 1 : 0; };

			$this->instructions['eqir'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($a == $vm->reg[$b]) ? 1 : 0; };
			$this->instructions['eqri'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($vm->reg[$a] == $b) ? 1 : 0; };
			$this->instructions['eqrr'] = function($vm, $a, $b, $c) { $vm->reg[$c] = ($vm->reg[$a] == $vm->reg[$b]) ? 1 : 0; };
		}

		public function getInstructions() {
			return array_keys($this->instructions);
		}

		public function setReg($reg) {
			$this->reg = $reg;
		}

		public function getReg() {
			return $this->reg;
		}

		public function setMap($map) {
			$this->map = $map;
		}

		public function run($instr, $args) {
			$instr = isset($this->map[$instr]) ? $this->map[$instr] : $instr;

			if (isset($this->instructions[$instr])) {
				array_unshift($args, $this);
				call_user_func_array($this->instructions[$instr], $args);
			}
		}
	}


	$sample = NULL;
	foreach ($input as $details) {
		if (preg_match('#Before: \[([0-9]+), ([0-9]+), ([0-9]+), ([0-9]+)\]#SADi', $details, $m)) {
			list($all, $a, $b, $c, $d) = $m;
			$sample = ['before' => [$a, $b, $c, $d], 'after' => [], 'input' => [], 'behaves' => []];
		} else if (preg_match('#After:  \[([0-9]+), ([0-9]+), ([0-9]+), ([0-9]+)\]#SADi', $details, $m)) {
			list($all, $a, $b, $c, $d) = $m;
			$sample['after'] = [$a, $b, $c, $d];

			$samples[] = $sample;
			$sample = NULL;
		} else if (preg_match('#([0-9]+) ([0-9]+) ([0-9]+) ([0-9]+)#SADi', $details, $m)) {
			list($all, $a, $b, $c, $d) = $m;

			if ($sample == NULL) {
				$test[] = [$a, $b, $c, $d];
			} else {
				$sample['instr'] = $a;
				$sample['args'] = [$b, $c, $d];
			}
		}
	}

	$vm = new VM();

	foreach ($samples as $s => $sample) {
		foreach ($vm->getInstructions() as $instr) {
			$vm->setReg($sample['before']);
			$vm->run($instr, $sample['args']);

			$match = ($vm->getReg() == $sample['after']);

			if (isDebug()) {
				echo '[', implode(', ', $sample['before']) ,'] => ', $instr, ' => [', implode(', ', $vm->getReg()), ']', ($match ? ' Matches: ' . implode(', ', $sample['after']) : ''), "\n";
			}

			if ($match) {
				$samples[$s]['behaves'][$instr] = true;
			}
		}
	}

	$part1 = 0;
	foreach ($samples as $sample) {
		if (count($sample['behaves']) >= 3) {
			$part1++;
		}
	}

	echo 'Part 1: ', $part1, "\n";


	$map = [];
	while (count($map) < 16) {
		foreach (array_keys($samples) as $s) {
			// Check if this sample only behaves like a single possible
			// instruction
			if (count($samples[$s]['behaves']) == 1) {
				$num = $samples[$s]['instr'];
				$name = array_keys($samples[$s]['behaves'])[0];
				if (isDebug()) { echo $num, ' only behaves like ', $name, "\n"; }

				// Add to the map
				$map[$num] = $name;

				// Remove from all other samples.
				foreach (array_keys($samples) as $s2) {
					unset($samples[$s2]['behaves'][$name]);
				}
			}
		}
	}

	$vm = new VM();
	$vm->setMap($map);
	foreach ($test as $args) {
		$instr = array_shift($args);
		$vm->run($instr, $args);
	}

	echo 'Part 2: ', $vm->getReg()[0], "\n";
