<?php
class LMC {
	private $accumulator;
	private $programCounter;
	private $instruction;
	private $RAM = [];
	private $Input;
	private $Output = "";
	private $logging;
	private $instructionCount = 0;
	private $negative;

	public function __construct(RAM $ram, Input $input, Output $output, Register $programCounter, Register $accumulator, Register $instruction, Flag $negative, $logging = false) {

		$this->RAM = $ram;
		$this->Output = $output;
		$this->Input = $input;
		$this->logging = $logging;

		// Set up registers
		$this->programCounter = $programCounter;
		$this->programCounter->set(0);
		$this->accumulator = $accumulator;
		$this->accumulator->set(0);
		$this->instruction = $instruction;
		$this->instruction->set(0);

		// Set up Flags
		$this->negative = $negative;
		$this->negative->set(false);
	}

	public function run() {
		while (true) {
			$this->instruction->set($this->RAM->get($this->programCounter->get()));
			$location = substr($this->instruction->get(),1,2);
			$this->programCounter->set($this->programCounter->get()+1);
			$this->instructionCount++; // For logging purposes.

			switch (substr($this->instruction->get(),0,1)) {

				case 0: // HLT
					$this->log("Halting");
					break 2;
				break;


				case 1: // ADD
					$val = $this->RAM->get($location);
					$accumulator = $this->accumulator->get();

					if ($this->negative->get()) {
						$accumulator = -$accumulator;
					}

					if ($accumulator+$val>0) {
						$this->negative->set(false); //If the sum of a negative accumulator + positive value then invert negative flag
					}

					$this->log("Adding Value $val from $location to accumulator");
					$this->accumulator->set($val + $this->accumulator->get()); // bug? getting accumulator again instead of $accumulator?
				break;


				case 2: // SUB
					$val = $this->RAM->get($location);
					$this->log("Subtracting Value $val from $location from the accumulator");
					$value = $this->accumulator->get() - $val;
					$this->negative->set(false);
					if ($value<0) {
						$this->negative->set(true);
						$value = -$value; // Negate to make positive
					}
					$this->accumulator->set($value);
				break;


				case 3: // STA
					$this->log("Setting RAM location '$location' to accumulator's value");
					$this->RAM->set($location,$this->accumulator->get());
					$this->negative->set(false); // If the accumulator isn't changing, why is this? Should be in LDA?
				break;


				case 5: // LDA
					$this->log("Load value at location $location into the accumulator");
					$this->accumulator->set($this->RAM->get($location));
				break;


				case 6: // BRA
					$this->log("Jump to $location");
					$this->programCounter->set($location);
				break;


				case 7: // BRZ
					if ($this->accumulator->get()==0) {
						$this->programCounter->set($location);
					}
					$this->log("Break if Accumulator = Zero to location $location");
				break;


				case 8: // BRP
					if (!$this->negative->get()) {
						$this->programCounter->set($location);
					}
					$this->log("Break if positive (negative flag is false) to location $location");
				break;


				case 9: // INP / OUT
					if ($location=="01") {
						$this->accumulator->set($this->Input->get());
						$this->log("Read Input to accumulator");
					}
					if ($location=="02") {
						$this->Output->set($this->accumulator->get());
						$this->log("Add accumulator to Output");
					}
				break;	
			}
		}
		$this->log("{$this->instructionCount} total instruction(s).");
	}

	private function log($string) {
		if ($this->logging) {
			echo $this->programCounter->get() . ": [" . $this->accumulator->get() . " / " . ($this->negative->get()?"-":"+") . "] $string\n";
		}
	}
}