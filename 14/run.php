#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$recipes = [3, 7];
	$elf1 = 0;
	$elf2 = 1;
	$offset = 0;
	$last = [];

	function addRecipe() {
		global $recipes, $elf1, $elf2, $last, $input, $offset;

		$new = $recipes[$elf1] + $recipes[$elf2];

		$res1 = floor($new / 10);
		$res2 = $new % 10;

		if ($res1 != 0) {
			$recipes[] = $res1;
			$last[] = $res1;
			if (count($last) > strlen($input)) {
				array_shift($last);
				yield implode('', $last);
			}
		}
		$recipes[] = $res2; $last[] = $res2;

		$elf1 += 1 + $recipes[$elf1];
		$elf1 %= count($recipes);

		$elf2 += 1 + $recipes[$elf2];
		$elf2 %= count($recipes);

		if (count($last) > strlen($input)) {
			array_shift($last);
			yield implode('', $last);
		}
	}


	$min = $input + 10;
	$part2 = null;
	for ($i = 0; ($i < $min || $part2 == null); $i++) {
		foreach (addRecipe() as $lastStr) {
			if ($part2 == null && $lastStr == $input) {
				$part2 = count($recipes) - strlen($input);
				echo 'Part 2: ', $part2, "\n";
			}
		}

		if ($i == $min - 1) {
			$part1 = implode('', array_slice($recipes, $input - $offset, 10));
			echo 'Part 1: ', $part1, "\n";
		}

	}
