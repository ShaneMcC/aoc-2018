#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$entries = explode(' ', $input);

	$nodeID = 0;
	$nodes = [];

	function parse($data, $pos = 0) {
		global $nodeID, $nodes;

		$startPos = $pos;

		$myID = $nodeID++;
		$childCount = $data[$pos++];
		$metadataCount = $data[$pos++];

		$node = ['children' => [], 'metadata' => []];
		for ($i = 0; $i < $childCount; $i++) {
			[$id, $endPos] = parse($data, $pos);
			// echo "\t", '[', $myID, '] Child at: ', ($pos + $i), ' - ', $endPos, "\n";
			$pos = $endPos;
			$node['children'][] = $id;
		}

		for ($i = 0; $i < $metadataCount; $i++) {
			// echo "\t", '[', $myID, '] Meta at: ', $pos, "\n";
			$node['metadata'][] = $data[$pos + $i];
		}
		$pos += $metadataCount;

		if (isDebug()) { echo 'Found node ', $myID, ' at pos: ', $startPos, ' with ', $childCount, ' children and ', $metadataCount, ' data ending at ', $pos, "\n"; }

		$nodes[$myID] = $node;
		return [$myID, $pos];
	}

	parse($entries);

	$part1 = 0;
	foreach ($nodes as $node) { $part1 += array_sum($node['metadata']); }

	echo 'Part 1: ', $part1, "\n";
