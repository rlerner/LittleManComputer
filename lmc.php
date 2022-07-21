<?php
// R.Lerner Little Man Computer (LMC) 2022/07/20

$inputs = [
	'7'
	,'10'
];


$file = file_get_contents("samples/add.lmc");
$program = explode("\n",$file);


$ram = new RAM;

// Load program into RAM
$loc = 0;
foreach ($program as $k => $v) {
	$ram->put($k,$v);
}

$input = new INPUT();
$input->put($inputs);

$output = new OUTPUT();
$lmc = new LMC($ram, $input, $output,true);
$lmc->run();

$x = $output->read();

print_r($x);


//------------------------------------------------------


class RAM {
	private $ram = [];

	public function put(int $location,int $value) {
		if ($location<0 || $location>99) {
			throw new Exception("Out of Bounds");
		}
		//todo value constraints?
		$this->ram[$location] = $value;
		return true;
	}

	public function read(int $location) {
		if ($location<0 || $location>99) {
			throw new Exception("Out of Bounds");
		}
		if (!isset($this->ram[$location])) {
			$this->ram[$location] = 0;
		}
		return $this->ram[$location];
	}
}

class INPUT {
	private $data = []; // Data for each input request
	private $readCounter = -1;

	public function read() {
		return $this->data[++$this->readCounter];
	}
	public function put($data) {
		$this->data = $data; // array of nums
	}
}

class OUTPUT {
	private $data = [];

	public function put($data) {
		$this->data[] = $data;
	}

	public function read() {
		return $this->data;
	}
}

class LMC {
	
	private $accumulator = 0;
	private $programCounter = 0;
	private $flagNegative = false;
	private $RAM = [];
	private $INPUT = "";
	private $OUTPUT = "";
	private $logging;

	public function __construct(RAM $ram, INPUT $input, OUTPUT $output,$logging = false) {

		$this->RAM = $ram;
		$this->OUTPUT = $output;
		$this->INPUT = $input;
		$this->logging = $logging;
	}

	public function run() {
		$run = 1;
		while ($run==1) {

			$program = $this->RAM->read($this->programCounter);
			$instruction = substr($program,0,1);
			$location = substr($program,1,2);
			$this->programCounter++;

			switch ($instruction) {

				case 0: // HLT

					$this->log("Halting");

					$run = 0;
				break;


				case 1: // ADD

					//TODO: Should the flagNegative be flipped if adding > 0? I think so but haven't seen an instance where I had to

					$val = $this->RAM->read($location);

					$this->log("Adding Value $val from $location to accumulator ({$this->accumulator})");

					$value = $val + $this->accumulator;

					if ($value>999) {
						throw new Exception("Overflow");
					}

					$this->accumulator = $value;
				break;


				case 2: // SUB

					$val = $this->RAM->read($location);

					$this->log("Subtracting Value $val from $location from the accumulator ({$this->accumulator})");

					$value = $this->accumulator - $val;

					if ($value<0) {
						$this->flagNegative = true;
						$value = -$value; // Negate to make positive
					} else {
						$this->flagNegative = false;
					}

					if ($value>999) {
						throw new Exception("Overflow");
					}

					$this->accumulator = $value;
				break;


				case 3: // STA
					$this->log("Setting accumulator to value at location $location");

					$this->RAM->put($location,$this->accumulator);
					//$this->flagNegative = false; //negative values can't be stored in a memory location, however shit freaks out if uncommented ¯\_ (ツ)_/¯ 
				break;

				case 4: // Undefined opcode
					$this->log("OMG OPCODE CLASS 4 INSTRUCTION '404' NOT FOUND BROO000011!");
				break;

				case 5: // LDA
					$this->log("Load value at location $location into the accumulator");
					$this->accumulator = $this->RAM->read($location);
				break;


				case 6: // BRA
					$this->log("Jump to $location");
					$this->programCounter = $location;
				break;


				case 7: // BRZ
					if ($this->accumulator==0) {
						$this->programCounter = $location;
					}
					$this->log("Break if Accumulator = Zero to location $location");
				break;


				case 8: // BRP
					if (!$this->flagNegative) {
						$this->programCounter = $location;
					}
					$this->log("Break if positive (flagNegative is false) to location $location");
				break;


				case 9: // INP / OUT
					if ($location=="01") {
						$this->accumulator = $this->INPUT->read();
						$this->log("Read Input to accumulator");
					}
					if ($location=="02") {
						$this->OUTPUT->put($this->accumulator);
						$this->log("Add accumulator to Output");
					}
				break;	
			}
		}
	}

	private function log($string) {
		if ($this->logging) {
			echo "{$this->programCounter}: [{$this->accumulator} / " . ($this->flagNegative?"+":"-") . "] $string\n";
		}
	}
}
