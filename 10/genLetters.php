#!/usr/bin/php
<?php

// From: https://www.reddit.com/r/adventofcode/comments/a4tbfl/trying_to_collect_all_used_letters_for_character/
$characters['A'] = <<<EOL
..##..
.#..#.
#....#
#....#
#....#
######
#....#
#....#
#....#
#....#
EOL;

$characters['B'] = <<<EOL
#####.
#....#
#....#
#....#
#####.
#....#
#....#
#....#
#....#
#####.
EOL;

$characters['C'] = <<<EOL
.####.
#....#
#.....
#.....
#.....
#.....
#.....
#.....
#....#
.####.
EOL;

$characters['E'] = <<<EOL
######
#.....
#.....
#.....
#####.
#.....
#.....
#.....
#.....
######
EOL;

$characters['F'] = <<<EOL
######
#.....
#.....
#.....
#####.
#.....
#.....
#.....
#.....
#...
EOL;

$characters['G'] = <<<EOL
.####.
#....#
#.....
#.....
#.....
#..###
#....#
#....#
#...##
.###.#
EOL;

$characters['H'] = <<<EOL
#....#
#....#
#....#
#....#
######
#....#
#....#
#....#
#....#
#....#
EOL;

$characters['J'] = <<<EOL
...###
....#.
....#.
....#.
....#.
....#.
....#.
#...#.
#...#.
.###..
EOL;

$characters['K'] = <<<EOL
#....#
#...#.
#..#..
#.#...
##....
##....
#.#...
#..#..
#...#.
#....#
EOL;

$characters['L'] = <<<EOL
#.....
#.....
#.....
#.....
#.....
#.....
#.....
#.....
#.....
######
EOL;

$characters['N'] = <<<EOL
#....#
##...#
##...#
#.#..#
#.#..#
#..#.#
#..#.#
#...##
#...##
#....#
EOL;

$characters['P'] = <<<EOL
#####.
#....#
#....#
#....#
#####.
#.....
#.....
#.....
#.....
#
EOL;

$characters['R'] = <<<EOL
#####.
#....#
#....#
#....#
#####.
#..#..
#...#.
#...#.
#....#
#....#
EOL;

$characters['X'] = <<<EOL
#....#
#....#
.#..#.
.#..#.
..##..
..##..
.#..#.
.#..#.
#....#
#....#
EOL;

$characters['Z'] = <<<EOL
######
.....#
.....#
....#.
...#..
..#...
.#....
#.....
#.....
######
EOL;


function charToID($character) {
	$id = '';
	foreach ($character as $bit) {
		if (is_array($bit)) { $bit = implode('', $bit); }
		$id .= sprintf('%02s', dechex(bindec(str_replace(['.', '#'], [0, 1], str_pad($bit, '8', '.')))));
	}
	return $id;
}


$encoded = [];
foreach ($characters as $c => $text) {
	$id = charToID(explode("\n", $text));
	$characters[$c] = $id;
	$encoded[$id] = $c;
}

echo '$encodedChars = ', var_export($encoded), ';', "\n";
