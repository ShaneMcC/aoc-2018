#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLines();

	function parseInput() {
		global $entries, $input;
		$entries = array();
		foreach ($input as $details) {
			preg_match('#(.*),(.*),(.*),(.*)#SADi', $details, $m);
			list($all, $x, $y, $z, $t) = $m;
			$entries[] = ['x' => trim($x), 'y' => trim($y), 'z' => trim($z), 't' => trim($t)];
		}
	}

	function manhattan4($x1, $y1, $z1, $t1, $x2, $y2, $z2, $t2) {
		return intval(abs($x1 - $x2)) + intval(abs($y1 - $y2)) + intval(abs($z1 - $z2)) + intval(abs($t1 - $t2));
	}

	function setConstellationID($entryID, $constellationId) {
		global $entries, $constellations;

		if (!isset($entries[$entryID]['constellation'])) {
			$entries[$entryID]['constellation'] = $constellationId;
			if (!isset($constellations[$constellationId])) { $constellations[$constellationId] = []; }
			$constellations[$constellationId][] = $entryID;

			foreach ($entries[$entryID]['linked'] as $id) {
				setConstellationID($id, $constellationId);
			}
		}
	}

	function findLinked($distance) {
		global $entries, $constellations;

		parseInput();

		// Find linked stars
		foreach ($entries as $id => $e) {
			$entries[$id]['linked'] = [];

			foreach ($entries as $id2 => $e2) {
				$m4 = manhattan4($e['x'], $e['y'], $e['z'], $e['t'], $e2['x'], $e2['y'], $e2['z'], $e2['t']);

				if ($m4 != 0 && $m4 <= $distance) {
					$entries[$id]['linked'][] = $id2;
				}
			}
		}

		foreach ($entries as $id => $e) {
			setConstellationID($id, $id);
		}
	}

	findLinked(3);
	echo 'Part 1: ', count($constellations), "\n";
