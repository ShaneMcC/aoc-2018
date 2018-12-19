#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../16/Day16VM.php');

	$input = getInputLines();
	$ip = explode(' ', array_shift($input))[1];

	class Day18VM extends Day16VM {
		public $stepCount = 0;

		public $ip = NULL;

		public function __construct($ip) {
			parent::__construct();
			$this->ip = $ip;
		}

		public function step() {
			// +1 Because this gets incremented by step.
			$this->setReg($this->ip, $this->location + 1);

			$res = parent::step();

			$this->location = $this->getReg($this->ip);

			$this->stepCount++;
			if ($this->stepCount > 5000) {
				die('Error.');
			}

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
	$vm->setDebug(isDebug());

	$wantedReg = 0;

	$vm->addReadAhead(function ($vm) use (&$wantedReg) {
		$loc = $vm->getLocation();
		$readAheadOps = 0;

		if (!$vm->hasData($loc + $readAheadOps)) { return FALSE; }
		$data = [];
		for ($i = 0; $i <= $readAheadOps; $i++) { $data[$i] = $vm->getData($loc + $i); }

		// Check for matching instructions.
		if ($data[0][0] == 'seti' && $data[0][1][2] == $vm->ip) {
			// Hax:
			$wanted = $vm->getReg($wantedReg);

			$divisors = [];
			for ($i = 1; $i <= $wanted; $i++) {
				if ($wanted % $i == 0) {
					$divisors[] = $i;
				}
			}

			$vm->setReg(0, array_sum($divisors));

			$vm->end(0);
			$vm->setReg($vm->ip, $vm->getLocation()); // Horrible IP Counter.
			return $vm->getLocation();
		}

		if ($data[0][0] == 'muli' && $data[0][1][1] == '11') {
			$wantedReg = $data[0][1][2];
		}

		return FALSE;
	});

	$vm->loadProgram(Day18VM::parseInstrLines($input));
	$vm->setRegisters([0, 0, 0, 0, 0, 0]);
	$vm->run();

	echo 'Part 1: ', $vm->getReg(0), "\n";

	$vm->loadProgram(Day18VM::parseInstrLines($input));
	$vm->setRegisters([1, 0, 0, 0, 0, 0]);
	$vm->run();
	echo 'Part 2: ', $vm->getReg(0), "\n";
