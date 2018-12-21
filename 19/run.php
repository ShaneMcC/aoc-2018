#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/Day19VM.php');

	$input = getInputLines();
	$ip = explode(' ', array_shift($input))[1];

	$vm = new Day19VM($ip);
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

	$vm->loadProgram(Day19VM::parseInstrLines($input));
	$vm->setRegisters([0, 0, 0, 0, 0, 0]);
	$vm->run();

	echo 'Part 1: ', $vm->getReg(0), "\n";

	$vm->loadProgram(Day19VM::parseInstrLines($input));
	$vm->setRegisters([1, 0, 0, 0, 0, 0]);
	$vm->run();
	echo 'Part 2: ', $vm->getReg(0), "\n";
