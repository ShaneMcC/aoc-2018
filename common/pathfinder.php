<?php
	/**
	 * Class used for searching mazes.
	 */
	class PathFinder {
		/** Initial State for solving from. */
		var $initialState = [];

		/** Our hooks */
		var $hooks = [];

		/**
		 * Create a new PathFinder
		 *
		 * @param $grid Array representing the grid we are searching in.
		 * @param $start Start location
		 * @param $end End location
		 */
		function __construct($grid, $start, $end) {
			$this->initialState = ['grid' => $grid, 'current' => $start, 'target' => $end, 'steps' => 0, 'previous' => []];

			$this->hooks['isAccessible'] = function($state, $x, $y) {
				return false;
			};

			$this->hooks['isValidLocation'] = function ($state, $x, $y) {
				list($curX, $curY) = $state['current'];
				if (!isset($state['grid'][$y][$x])) { return FALSE; } // Ignore Invalid
				if ($x != $curX && $y != $curY) { return FALSE; } // Ignore Corners
				if ($y == $curY && $x == $curX) { return FALSE; } // Ignore Current
				return TRUE;
			};

			$this->hooks['isFinished'] = function ($state) {
				return ($state['current'] == $state['target']);
			};
		}

		/**
		 * Set the hook function for the given hook.
		 *
		 * Valid Hooks:
		 *   'changeState' => Called after finding a new state.
		 *                    Gets passed [$oldState, $newState].
		 *                    $newState is replaced with the return value before
		 *                    being added to the possible options from getOptions();
		 *
		 *   'stateSorter' => Called after we've added the next set of possible
		 *                    states to our array to sort the array before the
		 *                    next loop.
		 *                    Gets passed [$states].
		 *                    $states is replaced with the return value before
		 *                    the next loop.
		 *
		 *   'isValidLocation' => Called to check if a position is a valid location
		 *                        to move to from the current location.
		 *                        Gets passed [$state, $x, $y].
		 *                        Default implementation assumes UDLR are valid.
		 *                        Return true if valid, else false.
		 *
		 *   'isAccessible' => Called to check if a position is accessible.
		 *                      Gets passed [$state, $x, $y].
		 *                      Default implementation assumes no positions are
		 *                      accessible.
		 *                      Return true if accessible, else false.
		 *
		 *   'isFinished' => Called to check if the given state is finished.
		 *                   Gets passed [$state].
		 *                   Default implementation assumes we are finished if
		 *                   current == target.
		 *                   Return true if finished, else false.
		 *
		 *   'solveStartState'  => Called before we begin solving.
		 *                         Gets passed [$beginState]
		 *
		 *   'solveNextState' => Called when we are checking a non-finished state.
		 *                       Gets passed [$state, $vistedLocations]
		 *
		 *   'solveFinishedState' => Called when we are at a finished state.
		 *                           Gets passed [$finalState, $vistedLocations]
		 *
		 * @param $hookPoint Name of hook point (from above list.)
		 * @param $function Function to call for this hook.
		 */
		function setHook($hookPoint, $function) {
			$this->hooks[$hookPoint] = $function;
		}

		/**
		 * Get the possible options to move on from the current state.
		 *
		 * @param $state State we start from.
		 * @return Array of new possible states.
		 */
		function getOptions($state) {
			list($curX, $curY) = $state['current'];

			$options = [];
			foreach ([$curX - 1, $curX, $curX + 1] as $x) {
				foreach ([$curY - 1, $curY, $curY + 1] as $y) {
					if (!call_user_func($this->hooks['isValidLocation'], $state, $x, $y)) { continue; }

					$new = [$x, $y];
					if (call_user_func($this->hooks['isAccessible'], $state, $x, $y) && !in_array($new, $state['previous'])) {
						$newState = $state;
						$newState['previous'][] = $newState['current'];
						$newState['current'] = $new;
						$newState['steps']++;

						if (isset($this->hooks['changeState'])) {
							$newState = call_user_func($this->hooks['changeState'], $state, $newState);
						}

						$options[] = $newState;
					}
				}
			}

			return $options;
		}

		/**
		 * Solve the maze.
		 *
		 * @param $maxSteps [default: none] Maximum number of steps to move
		 *                  before giving up.
		 * @return Array [$finalState, $visted]. $finalState will be FALSE if we
		 *         hit $maxSteps. $visted is all the [X, Y] locations we visted.
		 */
		function solveMaze($maxSteps = -1) {
			$beginState = $this->initialState;

			$visted = [$beginState['current']];
			$states = [$beginState];

			$finalState = FALSE;

			if (isset($this->hooks['solveStartState'])) { call_user_func($this->hooks['solveStartState'], $beginState); }

			while (count($states) > 0) {
				$state = array_shift($states);

				if ($maxSteps == -1 && call_user_func($this->hooks['isFinished'], $state)) {
					$finalState = $state;
					if (isset($this->hooks['solveFinishedState'])) { call_user_func($this->hooks['solveFinishedState'], $state, $visted); }
					break;
				} else {
					if (isset($this->hooks['solveNextState'])) { call_user_func($this->hooks['solveNextState'], $state, $visted); }
				}

				$options = $this->getOptions($state);
				foreach ($options as $opt) {
					if (!in_array($opt['current'], $visted) && ($maxSteps <= 0 || $opt['steps'] <= $maxSteps)) {
						$visted[] = $opt['current'];
						$states[] = $opt;
					}
				}

				if (isset($this->hooks['stateSorter'])) { $states = call_user_func($this->hooks['stateSorter'], $states); }
			}

			return [$finalState, $visted];
		}
	}
