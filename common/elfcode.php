#!/usr/bin/php
<?php
	$__NOHEADER = true;

	$__CLI['long'] = ['elffirst', 'noelf', 'alphareg'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --elffirst           Show code first not elfcode.';
	$__CLI['extrahelp'][] = '      --noelf              Don\'t show elfcode';
	$__CLI['extrahelp'][] = '      --alphareg           Convert registers to letters';

	require_once(dirname(__FILE__) . '/common.php');
	require_once(dirname(__FILE__) . '/../19/Day19VM.php');

	$input = getInputLines();

	$ipline = array_shift($input);
	$ip = explode(' ', $ipline)[1];

	$prog = Day19VM::parseInstrLines($input);

	$instrs = [];

	$instrs['addr'] = function($a, $b, $c) { return sprintf('r%s = r%s + r%s', $c, $a, $b); };
	$instrs['addi'] = function($a, $b, $c) { return sprintf('r%s = r%s + %s', $c, $a, $b); };

	$instrs['mulr'] = function($a, $b, $c) { return sprintf('r%s = r%s * r%s', $c, $a, $b); };
	$instrs['muli'] = function($a, $b, $c) { return sprintf('r%s = r%s * %s', $c, $a, $b); };

	$instrs['banr'] = function($a, $b, $c) { return sprintf('r%s = r%s & r%s', $c, $a, $b); };
	$instrs['bani'] = function($a, $b, $c) { return sprintf('r%s = r%s & %s', $c, $a, $b); };

	$instrs['borr'] = function($a, $b, $c) { return sprintf('r%s = r%s | r%s', $c, $a, $b); };
	$instrs['bori'] = function($a, $b, $c) { return sprintf('r%s = r%s | %s', $c, $a, $b); };

	$instrs['setr'] = function($a, $b, $c) { return sprintf('r%s = r%s', $c, $a); };
	$instrs['seti'] = function($a, $b, $c) { return sprintf('r%s = %s', $c, $a); };

	$instrs['gtir'] = function($a, $b, $c) { return sprintf('r%s = (%s > r%s)', $c, $a, $b); };
	$instrs['gtri'] = function($a, $b, $c) { return sprintf('r%s = (r%s > %s)', $c, $a, $b); };
	$instrs['gtrr'] = function($a, $b, $c) { return sprintf('r%s = (r%s > r%s)', $c, $a, $b); };

	$instrs['eqir'] = function($a, $b, $c) { return sprintf('r%s = (%s == r%s)', $c, $a, $b); };
	$instrs['eqri'] = function($a, $b, $c) { return sprintf('r%s = (r%s == %s)', $c, $a, $b); };
	$instrs['eqrr'] = function($a, $b, $c) { return sprintf('r%s = (r%s == r%s)', $c, $a, $b); };

	$elfCode = [];
	$converted = [];

	$registerMap = [];
	$diff = 0;
	for ($i = 0; $i <= max($ip, 6); $i++) {
		if ($i == $ip) {
			$registerMap['r' . $i] = 'ip';
			$diff -= 1;
		} else if (isset($__CLIOPTS['alphareg'])) {
			$registerMap['r' . $i] = chr(65 + $i + $diff);
		}
	}

	$i = 0;
	foreach ($prog as $p) {
		$code = call_user_func_array($instrs[$p[0]], $p[1]);
		$code = str_replace(array_keys($registerMap), array_values($registerMap), $code);

		if (preg_match('#^ip = (.*)#', $code, $m)) {
			$count = 0;
			$code = str_replace([' + ip', 'ip + '], '', $m[1], $count);

			if ($count > 0) {
				$code = 'jmp ' . $code;
			} else {
				$code = 'jmp @' . $code;
			}
		} else if (preg_match('#^(.*) = \1 ([+*&|]) (.*)#', $code, $m)) {
			$code = $m[1] . ' ' . $m[2] . '= ' . $m[3];
		} else if (preg_match('#^(.*) = (.*)([+*&|]) \1#', $code, $m)) {
			$code = $m[1] . ' ' . $m[3] . '= ' . $m[2];
		}


		$elfCode[$i] = $p[0] . ' ' . implode(' ', $p[1]);
		$converted[$i] = sprintf('%3s:    %s', $i, $code);

		$i++;
	}





	// Output.
	$colWidth = 40;

	if (isset($__CLIOPTS['noelf'])) {
		unset($__CLIOPTS['elffirst']);
	}

	if (!isset($__CLIOPTS['noelf'])) {
		if (isset($__CLIOPTS['elffirst'])) {
			echo $ipline, "\n";
		} else {
			echo str_repeat(' ', $colWidth), '# ', $ipline, "\n";
		}
	}

	for ($j = 0; $j < $i; $j++) {
		if (isset($__CLIOPTS['elffirst'])) {
			echo $elfCode[$j];
			echo str_repeat(' ', $colWidth - strlen($elfCode[$j])), '# ';
		}

		echo $converted[$j];

		if (!isset($__CLIOPTS['noelf']) && !isset($__CLIOPTS['elffirst'])) {
			echo str_repeat(' ', $colWidth - strlen($converted[$j])), '# ';
			echo $elfCode[$j];
		}

		echo "\n";
	}
