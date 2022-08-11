<?php
// LittleManComputer Assembler - (c)2022 Robert Lerner, All Rights Reserved. v.2022/07/26
class LMCAssembler {
	private $address = 0;
	private $intermediate = [];
	private $memoryMap = [];
	
	public $debug = false;
	public $nop400 = true;

	public $warnings = [];
	private $lastCommand = ""; // Used to track the last command to detect when DAT fields appear and ensure an HLT exists before it.
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
				// Ensures that there is seperation between data commands and instructions by verifying an HLT before DAT.
				if ($this->lastCommand!="DAT" && $this->lastCommand!="HLT") {				
					$this->warnings[] = "Program does not terminate with HLT before data fields encountered.";
				}

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
			$this->lastCommand = $command;
		}
	}

	public function assemble($assembly) {

		if ($this->nop400) { // Allow non-standard use of NOP, outputting a 400 code. Some LMC's will handle 400 differently so this reduces compatibility.
			$this->opcodes["400"] = "NOP";
		}

		$assembly = explode("\n",$assembly);

		foreach ($assembly as $row) {
			$row = explode("//",$row);
			$row = " " . trim(str_replace("\t"," ",$row[0])) . " ";

			if (substr(trim($row),0,1)=="!") { // Literal instruction, like !901 for INP, !201 to subtract an absolute address
				//todo, will require emulating checkrow here.
			}

			foreach ($this->opcodes as $k => $v) {
				$this->checkRow($row,$v);
			}
			$this->checkRow($row,"DAT"); // Not an opcode, but a weird LMC construct for assemblers
		}

		if ($this->debug) {
			echo "\n == MEMORY MAP ==\n";
			print_r($this->memoryMap);
			echo "\n == INTERMEDIATE ==\n";
			print_r($this->intermediate);
		}

		foreach ($this->intermediate as $row) {
			foreach ($this->memoryMap as $k => $v) {
				// Case insensitive, I'm fine with introducing this limitation
				$row = str_ireplace("%$k%",str_pad($v,2,"0",STR_PAD_LEFT),$row);
			}
			$out[] = $row;
		}
		
		if (count($out) > 100) {
			$this->warnings[] = "This program will use more than 100 memory cells, which an LMC cannot store.";
		}

		$finalCommand = array_pop($out);
		if ($finalCommand!="000") { //Works fine unless a final DAT is set to a non-zero value.
			$this->warnings[] = "This program does not have a final HLT, which may cause the program to treat data cells as instructions.";
		}
		array_push($out,$finalCommand);

		$out = implode("\n",$out);
		$comparative = str_replace("\n","",$out);

		if (preg_replace("/[^0-9]/", "",$comparative)!=$comparative) {
			$this->warnings[] = "Potential malformed output, non-numeric characters encountered.";
		}

		if (strlen($comparative)%3!==0) {
			$this->warnings[] = "Potential malformed output, character count not evenly divisible by 3.";
		}

		return $out;
	}


	public function outputSymbolTable() {
		$out = "";
		foreach($this->memoryMap as $k=>$v) {
			$out .= str_pad($v,3,"0",STR_PAD_LEFT) . ":$k\n";
		}
		return $out;
	}
}




/*

Makes a bug:
SWAPVAR	
		LDA a
		STA swap
		LDA b


Fixes the bug:

SWAPVAR	LDA a
		STA swap
		LDA b


Not seeing standalone labels properly and kicking out this:
8%SWAPVAR%

============

Ideas to add to asm interpreter:
#define VAR
#define VAR 123


 - Should we support COB which appears as an alternative to HLT in some docs?

 - Move undefined memory locations closest to the break, then trim off extra breaks so undefined DAT fields are not stored in the ASM (Optimize Memory Flag?)
 - Symbol table will be output as .sym, however, this will make reverse engineering quite easy.

*/