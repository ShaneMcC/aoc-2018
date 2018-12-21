<?php
	require_once(dirname(__FILE__) . '/../16/Day16VM.php');

	class Day19VM extends Day16VM {
		public $stepCount = 0;
		public $limit = 10000;

		public $ip = NULL;

		public function __construct($ip) {
			parent::__construct();
			$this->ip = $ip;
		}

		public function step() {
			// +1 Because this gets incremented by step.
			$this->setReg($this->ip, $this->location + 1);

			$res = parent::step();

			$this->location = $this->getReg($this->ip);

			$this->stepCount++;
			if ($this->limit != -1 && $this->stepCount >= $this->limit) {
				return FALSE;
			}

			return $res;
		}

		public function getSteps() {
			return $this->stepCount;
		}

		public function setLimit($limit) {
			$this->limit = $limit;
		}

		public function getLimit() {
			return $this->limit;
		}

		public static function parseInstrLines($input) {
			$data = array();
			foreach ($input as $in) {
				$bits = explode(' ', $in, 2);
				$data[] = [$bits[0], explode(' ', $bits[1])];
			}
			return $data;
		}
	}
