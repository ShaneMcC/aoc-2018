<?php
	/* Some of these are not memory efficient, so don't bother caring. */
	ini_set('memory_limit', '-1');

	/* Known Answers so far for comparing output after any changes. */
	require_once(dirname(__FILE__) . '/answers.php');

	/*
	 * To make code easier to read, sometimes we move "fluff" code to a separate
	 * file, include it if it exists.
	 *
	 * "Fluff" code is code that doesn't really serve to find the actual
	 * solution, but may instead do nice things with the output.
	 */
	if (file_exists(realpath(dirname($_SERVER['PHP_SELF'])) . '/fluff.php')) {
		require_once(realpath(dirname($_SERVER['PHP_SELF'])) . '/fluff.php');
	}

	/**
	 * Get the file to read input from.
	 * This will return php://stdin if we have something passed on stdin,
	 * else it will return the file passed on the cli as --file if present, if
	 * no file specified on the CLI then test mode uses 'test.txt' otherwise
	 * fallback to 'input.txt'
	 *
	 * @return Filename to read from.
	 */
	function getInputFilename() {
		global $__CLIOPTS;

		if (!posix_isatty(STDIN)) {
			return 'php://stdin';
		} else if (isset($__CLIOPTS['file']) && file_exists($__CLIOPTS['file'])) {
			return $__CLIOPTS['file'];
		}

		$default = realpath(dirname($_SERVER['PHP_SELF'])) . '/' . basename(isTest() ? 'test.txt' : 'input.txt');
		if (file_exists($default)) {
			return $default;
		}

		die('No valid input found.');
	}

	/**
	 * Get the input as an array of lines.
	 *
	 * @return File as an array of lines. Empty lines are ignored.
	 */
	function getInputLines() {
		return file(getInputFilename(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}

	/**
	 * Get the input as a single string.
	 *
	 * @return Whole file as a single string.
	 */
	function getInputContent() {
		return file_get_contents(getInputFilename());
	}

	/**
	 * Get the first line from the input.
	 *
	 * @return First line of input.
	 */
	function getInputLine() {
		$lines = getInputLines();
		return isset($lines[0]) ? trim($lines[0]) : '';
	}

	/**
	 * Are we running in debug mode?
	 *
	 * Debug mode usually results in more output.
	 *
	 * @return True for debug mode, else false.
	 */
	function isDebug() {
		global $__CLIOPTS;

		return isset($__CLIOPTS['d']) || isset($__CLIOPTS['debug']);
	}

	/**
	 * Echo something if we are running in debug mode.
	 */
	function debugOut() {
		if (isDebug()) {
			foreach (func_get_args() as $arg) { echo $arg; }
		}
	}

	/**
	 * Are we running in test mode?
	 *
	 * Test mode reads from test.txt not input.txt by default.
	 *
	 * @return True for test mode, else false.
	 */
	function isTest() {
		global $__CLIOPTS;

		return isset($__CLIOPTS['t']) || isset($__CLIOPTS['test']);
	}

	/**
	 * array_sum on multi-dimensional arrays.
	 *
	 * @param $array Array to sum.
	 * @return Sum of all vaules in array.
	 */
	function multi_array_sum($array) {
		$result = 0;
		foreach ($array as $a) { $result += (is_array($a) ? multi_array_sum($a) : $a); }
		return $result;
	}

	/**
	 * Generator to provide X/Y coordinates.
	 * X is returned as the Key, Y as the value
	 *
	 * @param $startx Starting X value
	 * @param $starty Starting Y value
	 * @param $endx Ending X value (inclusive)
	 * @param $endy Ending Y value (inclusive)
	 */
	function yieldXY($startx, $starty, $endx, $endy) {
		for ($x = $startx; $x <= $endx; $x++) {
			for ($y = $starty; $y <= $endy; $y++) {
				yield $x => $y;
			}
		}
	}

	/**
	 * Get all the permutations of an array of items.
	 * (From: http://stackoverflow.com/a/13194803/310353)
	 *
	 * @param $items Items to get permutations of.
	 * @param $perms Ignore this param, used for recursion when caclulating permutations.
	 * @return All permutations of $items;
	 */
	function getPermutations($items, $perms = array()) {
		if (empty($items)) {
			$return = array($perms);
		} else {
			$return = array();
			for ($i = count($items) - 1; $i >= 0; --$i) {
				$newitems = $items;
				$newperms = $perms;
				list($foo) = array_splice($newitems, $i, 1);
				array_unshift($newperms, $foo);
				$return = array_merge($return, getPermutations($newitems, $newperms));
			}
		}
		return $return;
	}

	/**
	 * Get all the possible combinations of $count numbers that add up to $sum
	 *
	 * @param $count Amount of values required in sum.
	 * @param $sum Sum we need to add up to
	 * @return Generator for all possible combinations.
	 */
	function getCombinations($count, $sum) {
	    if ($count == 1) {
			yield array($sum);
	    } else {
	        foreach (range(0, $sum) as $i) {
	            foreach (getCombinations($count - 1, $sum - $i) as $j) {
	                yield array_merge(array($i), $j);
	            }
	        }
		}
	}

	/**
	 * Get all the Sets of the given array.
	 *
	 * @param $array Array to get sets from.
	 * @param $maxLength Ignore sets larger than this size
	 * @param $minLength Ignore sets smaller than this size
	 * @return Array of sets.
	 */
	function getAllSets($array, $maxlength = PHP_INT_MAX, $minlength = 0) {
		$result = array(array());

		foreach ($array as $element) {
			foreach ($result as $combination) {
				$set = array_merge($combination, array($element));
				if (count($set) <= $maxlength) {
					$result[] = $set;
				}
			}
		}

		return $minlength <= 0 ? $result : array_filter($result, function ($a) use ($minlength) { return count($a) >= $minlength; });
	}

	/**
	 * modulus function that calculates the modulus of a number wrapping
	 * negative results backwards if required.
	 *
	 * @param $num Number
	 * @param $mod Modulus
	 * @return Answer.
	 */
	function wrapmod($num, $mod) {
		return (($num % $mod) + $mod) % $mod;
	}

	/**
	 * Sort an array using the given method, and return the result of the sort.
	 *
	 * @param $method Method to use for sorting (eg, 'arsort')
	 * @param $array Array to sort
	 * @param $extra (Default: null) Some of the sorting functions take an extra
	 *               param. (Flags or a function or so.)
	 * @return Sorted $array
	 */
	function sorted($method, $array, $extra = null) {
		call_user_func_array($method, ($extra == null) ? [&$array] : [&$array, $extra]);
		return $array;
	}

	/**
	 * Check if a string starts with another.
	 *
	 * @param $haystack Haystack to search
	 * @param $needle Needle to search for
	 * @return True if $haystack starts with $needle.
	 */
	function startsWith($haystack, $needle) {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * Check if a string ends with another.
	 *
	 * @param $haystack Haystack to search
	 * @param $needle Needle to search for
	 * @return True if $haystack ends with $needle.
	 */
	function endsWith($haystack, $needle) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * Get an ascii Wreath as a string.
	 * (Credit to 'jgs' for the original wreath ascii)
	 *
	 * @param $colour Colourise the wreath.
	 * @return The wreath
	 */
	function getWreath($colour = true) {
			$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

			if ($canColour) {
				$name = "\033[0m";
				$wreath = "\033[0;32m";
				$bow = "\033[1;31m";
				$berry = "\033[1;31m";
				$reset = "\033[0m";
			} else {
				$reset = $berry = $bow = $wreath = $name = '';
			}

			return <<<WREATH
$wreath           ,...., $reset
$wreath        ,;;:${berry}o$wreath;;;${berry}o$wreath;;, $reset
$wreath      ,;;${berry}o$wreath;'''''';;;;, $reset
$wreath     ,;:;;        ;;${berry}o$wreath;, $reset
$wreath     ;${berry}o$wreath;;          ;;;; $reset
$wreath     ;;${berry}o$wreath;          ;;${berry}o$wreath; $reset
$wreath     ';;;,  ${bow}_  _$wreath  ,;;;' $reset
$wreath      ';${berry}o$wreath;;$bow/_\/_\\$wreath;;${berry}o$wreath;' $reset
$name      $wreath  ';;$bow\_\/_/$wreath;;' $reset
$bow           '//\\\' $reset
$bow           //  \\\ $name     Advent of Code 2018 $reset
$bow          |/    \| $name    - ShaneMcC $reset
$reset

WREATH;
	}

	/**
	 * Get an ascii Tree as a string.
	 * (Credit to 'jgs' for the original tree ascii, this was modified to be
	 * taller)
	 *
	 * @param $colour Colourise the tree.
	 * @return The tree
	 */
	function getTree($colour = true) {
			$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

			if ($canColour) {
				$name = "\033[0m";
				$reset = "\033[0m";

				$star = "\033[1;33m";
				$tree = "\033[0;32m";
				$snow = "\033[1;37m";
				$box = "\033[1;30m";
				$led1 = "\033[1;31m";
				$led2 = "\033[1;34m";
				$led3 = "\033[1;35m";
				$led4 = "\033[1;36m";
			} else {
				$reset = $box = $star = $tree = $snow = $led1 = $led2 = $led3 = $led4 = $name = '';
			}

			return <<<TREE
$star             ' $reset
$star           - * - $reset
$tree            /.\ $reset
$tree           /..$led1'$tree\ $reset
$tree          /.$led2'$tree..$led4'$tree\ $reset
$tree          /$led1'$tree.$led3'$tree..\ $reset
$tree         /.$led2'$tree..$led1'$tree.$led4'$tree\ $reset
$tree        /.$led3'$tree..$led2'$tree.$led4'$tree.$led3'$tree\ $reset
$name       $tree /.$led4'$tree..$led1'$tree..$led1'$tree.\ $reset
$snow "'""""$tree/$led1'$tree.$led2'$tree...$led1'$tree..$led3'$tree.\\$snow""'"'" $reset
$tree      /$led2'$tree..$led1'$tree$led4'$tree..$led2'$tree.$led1'$tree.$led2'$tree.\ $name Advent of Code 2018 $reset
$tree      ^^^^^^${box}[_]$tree^^^^^^ $name - ShaneMcC $reset
$reset

TREE;
	}

	/**
	 * Get an ascii Santa as a string.
	 * (Credit to 'ldb' for the original Santa ascii, this was modified slightly)
	 *
	 * @param $colour Colourise the wreath.
	 * @return The wreath
	 */
	function getSanta($colour = true) {
			$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

			if ($canColour) {
				$name = "\033[0m";
				$beard = "\033[1;37m";
				$trim = "\033[1;37m";
				$hat = "\033[1;31m";
				$suit = "\033[1;31m";
				$hands = "\033[1;33m";
				$eyes = "\033[1;34m";
				$nose = "\033[1;31m";
				$buttons = "\033[1;37m";
				$belt = "\033[1;37m";
				$buckle = "\033[1;33m";
				$boots = "\033[1;37m";
				$reset = "\033[0m";
			} else {
				$name = $beard = $trim = $hat = $suit = $hands = $eyes = $nose = $buttons = $belt = $buckle = $boots = $reset = '';
			}

			return <<<SANTA
$hat             ,---.{$trim}_ $reset
$trim           _{$hat}/{$trim}_,_{$hat}\\{$trim}(_) $reset
$trim          (_,_,,_,) $reset
$beard           /$eyes o o {$beard}\ $reset
$beard          /'..{$nose}o{$beard}..'\ $reset
$suit      .--{$beard}(  ' , '  ){$suit}--. $reset
$suit     /  . {$beard}'-.....-'{$suit} .  \ $reset
$suit    (../{$belt}_____{$buttons} : {$belt}_____{$suit}\..) $reset
$hands    (_){$belt}(_____{$buckle}[-]{$belt}_____){$hands}(_) $reset
$suit        |     |     | $reset
$suit        ({$boots}.-.{$suit} / \\ {$boots}.-.{$suit}) $name Advent of Code 2018 $reset
$boots        (___)   (___) $name - ShaneMcC $reset
$reset

SANTA;
	}

	/**
	 * Get an ascii Present as a string.
	 * (Credit to 'jgs' for the original present ascii)
	 *
	 * @param $colour Colourise the present.
	 * @return The wreath
	 */
	function getPresent($colour = true) {
			$canColour = $colour && (function_exists('posix_isatty') && posix_isatty(STDOUT)) || getenv('ANSICON') !== FALSE;

			if ($canColour) {
				$name = "\033[0m";
				$box = "\033[0;32m";
				$bow = "\033[1;31m";
				$reset = "\033[0m";
			} else {
				$reset = $box = $bow = $name = '';
			}

			return <<<PRESENT
$bow           .__. $reset
$bow         .(\\\\//). $reset
$bow        .(\\\\()//). $reset
$box    .----${bow}(\\)\/(/)${box}----. $reset
$box    |${bow}     ///\\\\\ ${box}    | $reset
$box    |${bow}    ///||\\\\\ ${box}   | $reset
$box    |${bow}   //`||||`\\\\ ${box}  | $reset
$box    |${bow}      ||||${box}      | $reset
$box    |${bow}      ||||${box}      | $reset
$box    |${bow}      ||||${box}      | $reset
$box    |${bow}      ||||${box}      |$name Advent of Code 2018 $reset
$box    '------${bow}====${box}------'$name - ShaneMcC $reset
$reset

PRESENT;
	}

	/**
	 * Output one of the ascii headers.
	 *
	 * @param $colour Colourise the header.
	 * @return The header
	 */
	function getAsciiHeader($colour = true) {
		switch (rand(0,3)) {
			case 0: echo getPresent($colour); break;
			case 1: echo getSanta($colour); break;
			case 2: echo getWreath($colour); break;
			case 3: echo getTree($colour); break;
		}
	}

	try {
		$__CLI['short'] = "hdtw" . (isset($__CLI['short']) && is_array($__CLI['short']) ? implode('', $__CLI['short']) : '');
		$__CLI['long'] = array_merge(['help', 'file:', 'debug', 'test'], (isset($__CLI['long']) && is_array($__CLI['long']) ? $__CLI['long'] : []));
		$__CLIOPTS = @getopt($__CLI['short'], $__CLI['long']);
		if (isset($__CLIOPTS['h']) || isset($__CLIOPTS['help'])) {
			echo getAsciiHeader(), "\n";
			echo 'Usage: ', $_SERVER['argv'][0], ' [options]', "\n";
			echo '', "\n";
			echo 'Valid options:', "\n";
			echo '  -h, --help               Show this help output', "\n";
			echo '  -t, --test               Enable test mode (default to reading input from test.txt not input.txt)', "\n";
			echo '  -d, --debug              Enable debug mode', "\n";
			echo '      --file <file>        Read input from <file>', "\n";
			if (isset($__CLI['extrahelp']) && is_array($__CLI['extrahelp'])) {
				echo '', "\n";
				echo 'Additional script-specific options:', "\n";
				foreach ($__CLI['extrahelp'] as $line) { echo $line, "\n"; }
			}
			echo '', "\n";
			echo 'Input will be read from STDIN in preference to either <file> or the default files.', "\n";
			die();
		}
	} catch (Exception $e) { /* Do nothing. */ }
	if (!isset($__CLIOPTS['w'])) { echo getAsciiHeader(), "\n"; }
