#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../common/VM.php');
	$input = getInputLines();

	$samples = [];
	$test = [];

	class Day16VM extends VM {
		protected function init() {
			$this->instrs = [];
			$this->registers = [0, 0, 0, 0];

			$this->instrs['addr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) + $vm->getReg($b)); };
			$this->instrs['addi'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) + $b); };

			$this->instrs['mulr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) * $vm->getReg($b)); };
			$this->instrs['muli'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) * $b); };

			$this->instrs['banr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) & $vm->getReg($b)); };
			$this->instrs['bani'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) & $b); };

			$this->instrs['borr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) | $vm->getReg($b)); };
			$this->instrs['bori'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a) | $b); };

			$this->instrs['setr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $vm->getReg($a)); };
			$this->instrs['seti'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, $a); };

			$this->instrs['gtir'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($a > $vm->getReg($b)) ? 1 : 0); };
			$this->instrs['gtri'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($vm->getReg($a) > $b) ? 1 : 0); };
			$this->instrs['gtrr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($vm->getReg($a) > $vm->getReg($b)) ? 1 : 0); };

			$this->instrs['eqir'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($a == $vm->getReg($b)) ? 1 : 0); };
			$this->instrs['eqri'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($vm->getReg($a) == $b) ? 1 : 0); };
			$this->instrs['eqrr'] = function($vm, $args) { list($a, $b, $c) = $args; $vm->setReg($c, ($vm->getReg($a) == $vm->getReg($b)) ? 1 : 0); };
		}

		public function getInstructions() {
			return array_keys($this->instrs);
		}

		public function isReg($reg) {
			return array_key_exists($reg, $this->registers);
		}

		public function setRegisters($reg) {
			$this->registers = $reg;
		}

		public function getRegisters() {
			return $this->registers;
		}

		public function mapInstrs($map) {
			foreach ($map as $key => $value) {
				$this->instrs[$key] = $this->instrs[$value];
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
				$test[] = [$a, [$b, $c, $d]];
			} else {
				$sample['instr'] = $a;
				$sample['args'] = [$b, $c, $d];
			}
		}
	}

	$vm = new Day16VM();

	foreach ($samples as $s => $sample) {
		foreach ($vm->getInstructions() as $instr) {
			$vm->loadProgram([[$instr, $sample['args']]]);
			$vm->setRegisters($sample['before']);
			$vm->run();

			$match = ($vm->getRegisters() == $sample['after']);

			if (isDebug()) {
				echo '[', implode(', ', $sample['before']) ,'] => ', $instr, ' => [', implode(', ', $vm->getRegisters()), ']', ($match ? ' Matches: ' . implode(', ', $sample['after']) : ''), "\n";
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

	$vm = new Day16VM();
	$vm->loadProgram($test);
	$vm->setRegisters([0, 0, 0, 0]);
	$vm->mapInstrs($map);
	$vm->run();

	echo 'Part 2: ', $vm->getReg(0), "\n";
