#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$recipes = [3, 7];
	$elf1 = 0;
	$elf2 = 1;

	function addRecipe() {
		global $recipes, $elf1, $elf2;

		$new = $recipes[$elf1] + $recipes[$elf2];

		$res1 = floor($new / 10);
		$res2 = $new % 10;

		if ($res1 != 0) { $recipes[] = $res1; }
		$recipes[] = $res2;

		$elf1 += 1 + $recipes[$elf1];
		$elf1 %= count($recipes);

		$elf2 += 1 + $recipes[$elf2];
		$elf2 %= count($recipes);
	}


	for ($i = 0; $i < $input + 10; $i++) {
		addRecipe();
	}

	echo 'Part 1: ', implode('', array_slice($recipes, $input, 10)), "\n";
