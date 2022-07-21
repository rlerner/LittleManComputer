<?php
// R.Lerner - LMC Assembler - 2022/07/20

$file = file_get_contents($argv[1]);
$assembler = new LMCAssembler();
$output = $assembler->assemble($file);
$file = implode("\n",$output);
file_put_contents(str_ireplace(".asm","",$argv[1]) . ".lmc",$file);

class LMCAssembler {
	private $address = 0;
	private $intermediate = [];
	private $memoryMap = [];
	public $debug = false;
	private $opcodes = [
		"000" 	=> "HLT"
		,1 		=> "ADD"
		,2 		=> "SUB"
		,3 		=> "STA"
		,5 		=> "LDA"
		,6 		=> "BRA"
		,7 		=> "BRZ"
		,8 		=> "BRP"
		,"901"	=> "INP"
		,"902"	=> "OUT"
	];

	private function checkRow(string $row,string $command) {
		$row = strtoupper($row);
		if (strstr($row," $command ")!==false) {
			// Find anything to the left of the instruction as a line label
			// Find anything to the right to set the memory location this will map to
			$left = trim(substr($row,0,strpos($row," $command ")));
			$right = trim(substr($row,strpos($row," $command ")+5,strlen($row)));
			if ($left!="") {
				$this->memoryMap[$left] = $this->address;
			}

			if ($command=="DAT") {
				if ($left!="") {
					$this->memoryMap[$left] = $this->address;
					$this->intermediate[] = str_pad($right,3,"0",STR_PAD_LEFT);
				}
			} else {
				$opcode = array_search($command, $this->opcodes);
				if (strlen($opcode)==3) {
					$this->intermediate[] = $opcode;
				} else {
					$this->intermediate[] = $opcode . "%$right%";	
				}
			}

			$this->address++;
		}
	}

	public function assemble($assembly) {
		$assembly = explode("\n",$assembly);

		foreach ($assembly as $row) {
			$row = explode("//",$row);
			$row = " " . str_replace("\t"," ",$row[0]) . " ";
			foreach ($this->opcodes as $k => $v) {
				$this->checkRow($row,$v);
			}
			$this->checkRow($row,"DAT"); // Not an opcode, but a weird LMC construct for assemblers
		}

		if ($this->debug) {
			print_r($this->memoryMap);
			print_r($this->intermediate);
		}

		foreach ($this->intermediate as $row) {
			foreach ($this->memoryMap as $k => $v) {
				// Case insensitive, I'm fine with introducing this limitation
				$row = str_ireplace("%$k%",str_pad($v,2,"0",STR_PAD_LEFT),$row);
			}
			$out[] = $row;
		}
		return $out;
	}
}
