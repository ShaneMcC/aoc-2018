#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$entries = explode(' ', $input);

	$nodeID = 0;
	$nodes = [];
	$metaTotal = 0;

	function parse($data, $startPos = 0) {
		global $nodeID, $nodes, $metaTotal;

		$pos = $startPos;
		$myID = $nodeID++;
		$childCount = $data[$pos++];
		$metadataCount = $data[$pos++];

		$node = ['children' => [], 'metadata' => [], 'value' => 0];
		for ($i = 0; $i < $childCount; $i++) {
			list($id, $pos) = parse($data, $pos);
			$node['children'][] = $id;
		}

		for ($i = 0; $i < $metadataCount; $i++) {
			$meta = $data[$pos++];
			$node['metadata'][] = $meta;
			$metaTotal += $meta;

			if ($childCount == 0) {
				$node['value'] += $meta;
			} else {
				$child = $meta - 1;
				if (isset($node['children'][$child])) {
					$childID = $node['children'][$child];
					$node['value'] += $nodes[$childID]['value'];
				}
			}
		}

		$nodes[$myID] = $node;
		return [$myID, $pos];
	}
	parse($entries);

	echo 'Part 1: ', $metaTotal, "\n";
	echo 'Part 2: ', $nodes[0]['value'], "\n";
