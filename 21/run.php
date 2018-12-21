#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/../19/Day19VM.php');

	$input = getInputLines();
	$ip = explode(' ', array_shift($input))[1];

	$vm = new Day19VM($ip);
	$vm->setDebug(isDebug());
	$vm->setLimit(-1);

	$part1 = $part2 = 0;
	$seen = [];

	$vm->addReadAhead(function ($vm) use (&$part1, &$part2, &$seen) {
		$loc = $vm->getLocation();
		$readAheadOps = 0;

		if (!$vm->hasData($loc + $readAheadOps)) { return FALSE; }
		$data = [];
		for ($i = 0; $i <= $readAheadOps; $i++) { $data[$i] = $vm->getData($loc + $i); }

		// Check when we compare reg 0 to something.
		if ($data[0][0] == 'eqrr' && ($data[0][1][0] == 0 || $data[0][1][1] == 0)) {
			$wanted = $data[0][1][0] == 0 ? $data[0][1][1] : $data[0][1][0];
			$val = $vm->getReg($wanted);

			if ($part1 == 0) { $part1 = $val; }

			if (in_array($val, $seen)) {
				$part2 = $val;

				$vm->end(0);
				$vm->setReg($vm->ip, $vm->getLocation()); // Horrible IP Counter.
				return $vm->getLocation();
			}
			$seen[] = $val;
		}

		return FALSE;
	});


	$vm->loadProgram(Day19VM::parseInstrLines($input));
	$vm->setRegisters([0, 0, 0, 0, 0, 0]);
	$vm->run();

	echo 'Part 1: ', $part1, "\n";
	echo 'Part 2: ', $part2, "\n";
