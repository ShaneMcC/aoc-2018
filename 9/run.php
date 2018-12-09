#!/usr/bin/php
<?php
	require_once(dirname(__FILE__) . '/../common/common.php');
	$input = getInputLine();

	preg_match('#([0-9]+) players; last marble is worth ([0-9]+) points#SADi', $input, $m);
	list($all, $players, $lastMarble) = $m;

	class Marble {
		private $value;
		private $next;
		private $prev;

		public function __construct($value) {
			$this->value = $value;
			$this->next = $this;
			$this->prev = $this;
		}

		public function insertAfter($marble) {
			$marble->prev = $this;
			$marble->next = $this->next;

			$this->next->prev = $marble;
			$this->next = $marble;
		}

		public function next() {
			return $this->next;
		}

		public function prev() {
			return $this->prev;
		}

		public function remove() {
			$this->prev->next = $this->next;
			$this->next->prev = $this->prev;
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

		private $marbles;
		private $currentMarble;

		private $currentPlayer = 0;
		private $nextMarble = 1;

		public function __construct($players) {
			$this->players = array_fill(0, $players, 0);

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

		public function play($lastMarble) {
			while ($this->nextMarble < $lastMarble) {
				$this->placeMarble($this->nextMarble++, $this->currentPlayer);
				if (isDebug()) { $this->display($this->currentPlayer); }

				$this->currentPlayer = ($this->currentPlayer + 1) % count($this->players);
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

	// Urgh.
	//
	// Garbage collector can't handle too many nested objects, so let's just
	// disable it. ¯\_(ツ)_/¯
	//
	// https://bugs.php.net/bug.php?id=72411 => https://bugs.php.net/bug.php?id=68606
	gc_disable();

	$game = new Game($players);

	echo 'Part 1: ', $game->play($lastMarble)->getBestScore(), "\n";

	if (!isTest()) {
		echo 'Part 2: ', $game->play($lastMarble * 100)->getBestScore(), "\n";
	}
