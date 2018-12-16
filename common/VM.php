<?php

	/**
	 * Simple VM
	 */
	class VM {
		/** Current location. */
		protected $location = -1;

		/** Known Instructions. */
		protected $instrs = array();

		/** Internal Registers. */
		protected $registers = array();

		/** Data to execute. */
		protected $data = array();

		/** Read ahead optimisations */
		protected $readAheads = array();

		/** Our exit code. */
		protected $exitCode = 0;

		/** Output from the VM. */
		protected $output = '';

		/** VM Misc Data. */
		protected $miscData = [];

		/** Is debug mode enabled? */
		protected $debug = false;

		/** Sleep time between debug output. */
		protected $sleep = 25000;

		/**
		 * Create a new VM.
		 *
		 * @param $data (Optional) Program execution data.
		 */
		function __construct($data = array()) {
			$this->init();
			$this->loadProgram($data);
		}

		/**
		 * Load in a new program and reset the VM State.
		 *
		 * @param $data Data to load.
		 */
		function loadProgram($data) {
			$this->data = $data;
			$this->reset();
		}

		/**
		 * Reset the VM.
		 *
		 * This resets the registers to all-0 and moves the location to the
		 * beginning.
		 */
		function reset() {
			$this->exitCode = 0;
			$this->location = -1;
			$this->registers = array();
			$this->clearOutput();
		}

		/**
		 * End program execution.
		 *
		 * This sets the location to beyond the data range, effectively
		 * stopping execution.
		 *
		 * @param $exitCode Set the exit code.
		 */
		function end($exitCode = 0) {
			$this->location = count($this->data);
			$this->exitCode = $exitCode;
		}

		/**
		 * Get the vm exit code.
		 *
		 * @return The program exit code.
		 */
		function exitCode() {
			return $this->exitCode;
		}

		/**
		 * Clear stored output.
		 */
		public function clearOutput() {
			$this->output = '';
		}

		/**
		 * Get stored output.
		 *
		 * @return The stored output.
		 */
		public function getOutput() {
			return $this->output;
		}

		/**
		 * Get the length of the stored output.
		 *
		 * @return The length of the stored output.
		 */
		public function getOutputLength() {
			return strlen($this->output);
		}

		/**
		 * Append data to the output.
		 *
		 * @param $str String to append to output.
		 */
		public function appendOutput($str) {
			$this->output .= $str;
		}

		/**
		 * Set the output data.
		 *
		 * @param $str String to set output as.
		 */
		public function setOutput($str) {
			$this->output = $str;
		}

		/**
		 * Get MISC VM Data.
		 *
		 * @param $data Data type
		 * @return Data value or NULL
		 */
		public function getMiscData($data) {
			return isset($this->miscData[$data]) ? $this->miscData[$data] : null;
		}

		/**
		 * Set MISC VM Data.
		 *
		 * @param $data Data type
		 * @param $value Data value
		 */
		public function setMiscData($data, $value) {
			$this->miscData[$data] = $value;
			return $this;
		}

		/**
		 * Set the value of debugging.
		 *
		 * @param $debug New value for debugging.
		 * @param $sleep (Default: 25000) Time between debug output lines (NULL not to change)
		 */
		public function setDebug($debug, $sleep = NULL) {
			$this->debug = $debug;
			if ($sleep !== NULL) {
				$this->sleep = $sleep;
			}
		}

		/**
		 * Get the instruction function by the given name.
		 *
		 * @param $instr Instruction name.
		 * @return Instruction function.
		 */
		public function getInstr($instr) {
			if (isset($this->instrs[$instr])) { return $this->instrs[$instr]; }
			throw new Exception('Unknown Instr: ' . $instr);
		}

		/**
		 * Set the instruction by the given name to the given function.
		 *
		 * @param $instr Instruction name.
		 * @param $function New function.
		 * @return Instruction function.
		 */
		public function setInstr($instr, $function) {
			$this->instrs[$instr] = $function;
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location.
		 * @return Data from location.
		 */
		public function hasData($loc) {
			return isset($this->data[$loc]);
		}

		/**
		 * Get the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @return Data from location.
		 */
		public function getData($loc = null) {
			if ($loc == null) { $loc = $this->getLocation(); }
			if (isset($this->data[$loc])) { return $this->data[$loc]; }
			throw new Exception('Unknown Data Location: ' . $loc);
		}

		/**
		 * Set the data at the given location.
		 *
		 * @param $location Data location (or NULL for current).
		 * @param $val New Value
		 */
		public function setData($loc, $val) {
			if ($loc == null) { $loc = $this->getLocation(); }
			if (isset($this->data[$loc])) {
				$this->data[$loc] = $val;
			} else {
				throw new Exception('Unknown Data Location: ' . $loc);
			}
		}

		/**
		 * Iniit the Instructions.
		 */
		protected function init() {
			/**
			 * set
			 *   - set X Y
			 *
			 * sets register X to the value of Y.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['set'] = function($vm, $args) {
				$x = $args[0];
				$y = $vm->getValue($args[1]);

				$vm->setReg($x, $y);
			};

			/**
			 * add
			 *   - add X Y
			 *
			 * increases register X by the value of Y.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['add'] = function($vm, $args) {
				$x = $args[0];
				$y = $vm->getValue($args[1]);

				$vm->setReg($x, $vm->getReg($x) + $y);
			};

			/**
			 * sub
			 *   - sub X Y
			 *
			 * decreases register X by the value of Y.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['sub'] = function($vm, $args) {
				$x = $args[0];
				$y = $vm->getValue($args[1]);

				$vm->setReg($x, $vm->getReg($x) - $y);
			};

			/**
			 * mul
			 *   - mul X Y
			 *
			 * sets register X to the result of multiplying the value contained
			 * in register X by the value of Y.
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['mul'] = function($vm, $args) {
				$x = $args[0];
				$y = $vm->getValue($args[1]);

				$vm->setReg($x, $vm->getReg($x) * $y);
			};

			/**
			 * mod
			 *   - mod X Y
			 *
			 * sets register X to the remainder of dividing the value contained
			 * in register X by the value of Y (that is, it sets X to the
			 * result of X modulo Y).
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['mod'] = function($vm, $args) {
				$x = $args[0];
				$y = $vm->getValue($args[1]);

				$vm->setReg($x, $vm->getReg($x) % $y);
			};

			/**
			 * jgz
			 *   - jgz X Y
			 *
			 * jumps with an offset of the value of Y, but only if the value
			 * of X is greater than zero. (An offset of 2 skips the next
			 * instruction, an offset of -1 jumps to the previous instruction,
			 * and so on.)
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['jgz'] = function($vm, $args) {
				$x = $vm->getValue($args[0]);
				$y = $vm->getValue($args[1]);

				if ($x > 0) {
					$newloc = $vm->getLocation() + (int)$y;
					$vm->jump($newloc - 1); // (-1 because step() always does +1)
				}
			};

			/**
			 * jnz
			 *   - jnz X Y
			 *
			 * jumps with an offset of the value of Y, but only if the value
			 * of X is not zero. (An offset of 2 skips the next instruction, an offset
			 * of -1 jumps to the previous instruction, and so on.)
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['jnz'] = function($vm, $args) {
				$x = $vm->getValue($args[0]);
				$y = $vm->getValue($args[1]);

				if ($x != 0) {
					$newloc = $vm->getLocation() + (int)$y;
					$vm->jump($newloc - 1); // (-1 because step() always does +1)
				}
			};

			/**
			 * jlz
			 *   - jlz X Y
			 *
			 * jumps with an offset of the value of Y, but only if the value
			 * of X is less than zero. (An offset of 2 skips the next
			 * instruction, an offset of -1 jumps to the previous instruction,
			 * and so on.)
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['jlz'] = function($vm, $args) {
				$x = $vm->getValue($args[0]);
				$y = $vm->getValue($args[1]);

				if ($x < 0) {
					$newloc = $vm->getLocation() + (int)$y;
					$vm->jump($newloc - 1); // (-1 because step() always does +1)
				}
			};


			/**
			 * jez
			 *   - jez X Y
			 *
			 * jumps with an offset of the value of Y, but only if the value
			 * of X is equal to zero. (An offset of 2 skips the next
			 * instruction, an offset of -1 jumps to the previous instruction,
			 * and so on.)
			 *
			 * @param $vm VM to execute on.
			 * @param $args Args for this instruction.
			 */
			$this->instrs['jez'] = function($vm, $args) {
				$x = $vm->getValue($args[0]);
				$y = $vm->getValue($args[1]);

				if ($x == 0) {
					$newloc = $vm->getLocation() + (int)$y;
					$vm->jump($newloc - 1); // (-1 because step() always does +1)
				}
			};
		}

		/**
		 * Get the current execution location.
		 *
		 * @return Location of current execution.
		 */
		function getLocation() {
			return $this->location;
		}

		/**
		 * Jump to specific location.
		 *
		 * @param $loc Location to jump to.
		 */
		function jump($loc) {
			$this->location = $loc;
		}

		/**
		 * Step a single instruction.
		 *
		 * @return True if we executed something, else false if we have no more
		 *         to execute.
		 */
		function step() {
			if (isset($this->data[$this->location + 1])) {
				$this->location++;

				$optimise = $this->doReadAheads();
				// If we optimised, assume we did something, and then we'll
				// continue in he next step
				if ($optimise !== false) {
					// -1 because step() does ++
					$this->location = $optimise - 1;
					return TRUE;
				}

				$next = $this->data[$this->location];
				if ($this->debug) {
					if (isset($this->miscData['pid'])) {
						echo sprintf('[PID: %2s] ', $this->miscData['pid']);
					}
					echo sprintf('(%4s)   %-20s %s', $this->location, VM::instrToString($next), $this->dumpReg()), "\n";
					usleep($this->sleep);
				}
				list($instr, $data) = $next;
				$ins = $this->getInstr($instr);
				$ins($this, $data);

				return TRUE;
			} else {
				return FALSE;
			}
		}

		/**
		 * Read ahead in the script to optimise where possible.
		 * This is called AFTER the location pointer has been moved, but before
		 * the instruction is read.
		 *
		 * Optimisations can either edit the instructions and allow continued
		 * execution (return $vm->getLocation()) or can perform the required
		 * state manipulation themselves and provide a new location to continue
		 * from.
		 *
		 * We stop processing optimisations after the first non-FALSE return.
		 *
		 * @return FALSE if no optimisations were made, else a location index
		 *         for the next instruction we should run.
		 */
		function doReadAheads() {
			foreach ($this->readAheads as $function) {
				$return = call_user_func($function, $this);
				if ($return !== FALSE && $return !== NULL) { return $return; }
			}
			return FALSE;
		}

		/**
		 * Add a new ReadAhead optimiser for doReadAheads to use.
		 *
		 * @param $function Function to call in doReadAheads, should accept 1
		 *        parameter (which will be $this)  and return FALSE if no
		 *        optimisation occured, or a new location to continue execution
		 *        from.
		 */
		function addReadAhead($function) {
			$this->readAheads[] = $function;
		}

		/**
		 * Continue stepping through untill we reach the end.
		 */
		function run() {
			while ($this->step()) { }
		}

		/**
		 * Check if the given input is a valid register.
		 *
		 * @param $reg Register to check
		 * @return True if valid register.
		 */
		function isReg($reg) {
			if (preg_match('#^[a-z]$#i', $reg)) {
				if (!isset($this->registers[strtolower($reg)])) {
					$this->registers[strtolower($reg)] = 0;
				}
				return true;
			}

			return false;
		}

		/**
		 * Get the value of the given register.
		 *
		 * @param $reg Register to get value of
		 * @return Value of $reg
		 */
		function getReg($reg) {
			if ($this->isReg($reg)) { return $this->registers[$reg]; }
			throw new Exception('Unknown Register: ' . $reg);
		}

		/**
		 * Get the value of the given input.
		 * If $value is a valid register, the register value will be returned,
		 * else $value will be returned.
		 *
		 * @param $value Value or register name.
		 * @return $value or value of $value register
		 */
		function getValue($value) {
			if ($this->isReg($value)) { return $this->registers[$value]; }
			return $value;
		}

		/**
		 * Set the value of the given register.
		 *
		 * @param $reg Register to Set value of
		 * @param $val Value to set register to.
		 */
		function setReg($reg, $val) {
			if ($this->isReg($reg)) { $this->registers[$reg] = $val; return $val; }
			throw new Exception('Unknown Register: ' . $reg);
		}

		/**
		 * Set the value of the given register.
		 *
		 * @param $reg Register to Set value of
		 * @param $val Value to set register to.
		 */
		function dumpReg() {
			$out = [];
			foreach ($this->registers as $reg => $val) {
				$out[] = sprintf('%s: %-5s', $reg, $val);
			}
			return '[' . implode('] [', $out) . ']';
		}

		/**
		 * Parse instruction file into instruction array.
		 *
		 * @param $data Data to parse/
		 */
		public static function parseInstrLines($input) {
			$data = array();
			foreach ($input as $lines) {
				if (preg_match('#([a-z]{3}) ([^\s]+)(?: (.*))?#SADi', $lines, $m)) {
					$data[] = array($m[1], array_slice($m, 2));
				}
			}
			return $data;
		}

		/**
		 * Display an instruction as a string.
		 *
		 * @param $instr Instruction to get string representation for.
		 * @return  String version of instruction.
		 */
		public static function instrToString($instr) {
			return $instr[0] . ' [' . implode(' ', $instr[1]) . ']';
		}
	}
