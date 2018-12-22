#!/usr/bin/php
<?php
	$__NOHEADER = true;

	$__CLI['long'] = ['codefirst'];
	$__CLI['extrahelp'] = [];
	$__CLI['extrahelp'][] = '      --codefirst          Show code first not elfcode.';

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

	$i = 0;
	if (!isset($__CLIOPTS['codefirst'])) {
		echo $ipline, "\n";
	} else {
		echo str_repeat(' ', 38), '# ', $ipline, "\n";
	}

	foreach ($prog as $p) {
		$line = $p[0] . ' ' . implode(' ', $p[1]);

		if (!isset($__CLIOPTS['codefirst'])) {
			echo $line, str_repeat(' ', 38 - strlen($line)), '# ';
		}

		echo sprintf('%3s:    ', $i);

		$code = call_user_func_array($instrs[$p[0]], $p[1]);
		$code = str_replace('r' . $ip, 'ip', $code);


		if (preg_match('#^\ip = (.*)#', $code, $m)) {
			$count = 0;
			$code = str_replace([' + ip', 'ip + '], '', $m[1], $count);

			if ($count > 0) {
				$code = 'jmp ' . $code;
			} else {
				$code = 'jmp @' . $code;
			}
		}

		echo $code;

		if (isset($__CLIOPTS['codefirst'])) {
			echo str_repeat(' ', 30 - strlen($code)), '# ', $line;
		}

		echo "\n";
		$i++;
	}
