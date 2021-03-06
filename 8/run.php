#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	$entries = explode(' ', $input);

	function parse($data, $startPos = 0) {
		$pos = $startPos;
		$childCount = $data[$pos++];
		$metadataCount = $data[$pos++];

		$node = ['children' => [], 'metadata' => [], 'value' => 0, 'metavalue' => 0];
		for ($i = 0; $i < $childCount; $i++) {
			list($child, $pos) = parse($data, $pos);
			$node['children'][] = $child;
			$node['metavalue'] += $child['metavalue'];
		}

		for ($i = 0; $i < $metadataCount; $i++) {
			$meta = $data[$pos++];
			$node['metadata'][] = $meta;
			$node['metavalue'] += $meta;

			if ($childCount == 0) {
				$node['value'] += $meta;
			} else {
				$child = $meta - 1;
				if (isset($node['children'][$child])) {
					$node['value'] += $node['children'][$child]['value'];
				}
			}
		}

		return [$node, $pos];
	}
	list($root, $endpos) = parse($entries);

	echo 'Part 1: ', $root['metavalue'], "\n";
	echo 'Part 2: ', $root['value'], "\n";
