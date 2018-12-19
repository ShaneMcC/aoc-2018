<?php
	require_once(dirname(__FILE__) . '/../common/VM.php');

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
