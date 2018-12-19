#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	require_once(dirname(__FILE__) . '/Day16VM.php');
	$input = getInputLines();

	$samples = [];
	$test = [];




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
