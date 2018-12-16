#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	$samples = [];
	$test = [];

	class VM {
		private $instructions = [];
		private $reg;

		public function __construct() {
			$this->init();
		}

		private function init() {
			$this->reg = [0, 0, 0, 0];
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

		public function run($instr, $args) {
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
			$sample = ['before' => [$a, $b, $c, $d], 'after' => [], 'input' => [], 'instrs' => []];
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
				$sample['input'] = [$a, $b, $c, $d];
			}
		}
	}


	$vm = new VM();

	foreach ($samples as $s => $sample) {
		foreach ($vm->getInstructions() as $instr) {
			$vm->setReg($sample['before']);

			$args = $sample['input'];
			array_shift($args);

			$vm->run($instr, $args);

			$match = ($vm->getReg() == $sample['after']);

			if (isDebug()) {
				echo '[', implode(', ', $sample['before']) ,'] => ', $instr, ' => [', implode(', ', $vm->getReg()), ']', ($match ? ' Matches: ' . implode(', ', $sample['after']) : ''), "\n";
			}

			if ($match) {
				$samples[$s]['instrs'][] = $instr;
			}
		}
	}

	$part1 = 0;
	foreach ($samples as $sample) {
		if (count($sample['instrs']) >= 3) {
			$part1++;
		}
	}

	echo 'Part 1: ', $part1, "\n";
