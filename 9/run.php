#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	preg_match('#([0-9]+) players; last marble is worth ([0-9]+) points#SADi', $input, $m);
	list($all, $players, $lastMarble) = $m;

	class Marble {
		private static $marbles = [];

		private $value;
		private $next;
		private $prev;

		public function __construct($value) {
			$this->value = $value;
			$this->next = $value;
			$this->prev = $value;

			Marble::$marbles[$value] = $this;
		}

		public function insertAfter($marble) {
			Marble::$marbles[$marble->value()]->prev = $this->value();
			Marble::$marbles[$marble->value()]->next = $this->next;

			Marble::$marbles[$this->next]->prev = $marble->value();
			$this->next = $marble->value();
		}

		public function next() {
			return Marble::$marbles[$this->next];
		}

		public function prev() {
			return Marble::$marbles[$this->prev];
		}

		public function remove() {
			Marble::$marbles[$this->prev]->next = $this->next;
			Marble::$marbles[$this->next]->prev = $this->prev;
		}

		public function value() {
			return $this->value;
		}

		public function __toString() {
			return (String)$this->value();
		}
	}

	class Game {
		private $players;
		private $currentPlayer = '-';

		private $lastMarble;
		private $marbles;
		private $currentMarble;

		public function __construct($players, $lastMarble) {
			$this->players = array_fill(0, $players, 0);
			$this->lastMarble = $lastMarble;

			$this->currentMarble = $this->marbles = new Marble(0);
		}

		public function display($currentPlayer = '-') {
			$marble = $this->marbles;

			echo '[', $currentPlayer, '] ';
			do {
				echo ' ';
				echo $this->currentMarble == $marble ? '(' : ' ';
				echo $marble;
				echo $this->currentMarble == $marble ? ')' : ' ';

				$marble = $marble->next();
			} while ($marble != $this->marbles);
			echo "\n";
		}

		public function placeMarble($id, $player) {
			if ($id % 23 == 0) {
				$this->players[$player] += $id;

				// Get the 7th-previous marble.
				$prev = $this->currentMarble->prev()->prev()->prev()->prev()->prev()->prev()->prev();

				// Change the pointer.
				$this->currentMarble = $prev->next();

				// Remove it.
				$prev->remove();

				// Add the score.
				$this->players[$player] += $prev->value();
			} else {
				$new = new Marble($id);
				$this->currentMarble->next()->insertAfter($new);
				$this->currentMarble = $new;
			}
		}

		public function play() {
			$currentPlayer = 0;
			$nextMarble = 1;
			while (true) {
				$this->placeMarble($nextMarble++, $currentPlayer);

				if (isDebug()) { $this->display($currentPlayer); }

				if ($nextMarble >= $this->lastMarble) { break; }

				$currentPlayer = ($currentPlayer + 1) % count($this->players);
			}

			return $this;
		}

		public function getScores() {
			return $this->players;
		}

		public function getBestScore() {
			return max($this->players);
		}
	}

	// echo (new Game(9, 26))->play()->getBestScore();
	echo 'Part 1: ', (new Game($players, $lastMarble))->play()->getBestScore(), "\n";

	if (!isTest()) {
		echo 'Part 2: ', (new Game($players, $lastMarble * 100))->play()->getBestScore(), "\n";
	}
