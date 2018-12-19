#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../16/Day16VM.php');

	$input = getInputLines();
	$ip = explode(' ', array_shift($input))[1];

	class Day18VM extends Day16VM {
		protected $ip = NULL;

		public function __construct($ip) {
			parent::__construct();
			$this->ip = $ip;
		}

		public function step() {
			// +1 Because this gets incremented by step.
			$this->setReg($this->ip, $this->location + 1);

			$res = parent::step();

			$this->location = $this->getReg($this->ip);

			return $res;
		}

		public static function parseInstrLines($input) {
			$data = array();
			foreach ($input as $in) {
				$bits = explode(' ', $in, 2);
				$data[] = [$bits[0], explode(' ', $bits[1])];
			}
			return $data;
		}
	}

	$vm = new Day18VM($ip);
	$vm->loadProgram(Day18VM::parseInstrLines($input));
	$vm->setRegisters([0, 0, 0, 0, 0, 0]);
	$vm->setDebug(isDebug());
	$vm->run();

	echo 'Part 1: ', $vm->getReg(0), "\n";
