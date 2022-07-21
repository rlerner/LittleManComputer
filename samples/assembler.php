<?php

/*
Translates "Little Man Computer" (LMC) Mnemoinics to OpCodes, handles variable names and labels, and outputs machine code.

HOW TO RUN: php assembler.php [FILENAME.ASM]
OUTPUT: FILENAME.LMC


Example:

START	BRK
		
END		OUT
		HLT


LABEL 	MNEOMNIC 	VALUE


Variables should be defined at the end of the program (past the past BRK) as follows:

Uninitalized:
VARNAME	DAT

Initalized to "1":

VARNAME DAT 1


Limitations:
	- Line labels and variables cannot have the same name since they are stored in one mapping table and string replaced. It can be improved but nah.
	- Variables are case-insensitive.
	- There's always 1+ trailing BRK (000) Opcode

*/



$file = file_get_contents($argv[1]);
$file = LMCAssembler($file);
$file = implode("\n",$file);
file_put_contents(str_ireplace(".asm","",$argv[1]) . ".lmc",$file);

function LMCAssembler($assembly) {
	$assembly = explode("\n",$assembly);
	$memoryMapper = [];
	$memLocation = 0;

	foreach ($assembly as $row) {
		$left = $right = "";
		
		// Drop off comments, replace tabs with spaces, pad each row with spaces for instruction comparison
		$row = explode("//",$row);
		$row = " " . str_replace("\t"," ",$row[0]) . " ";

		if (stristr($row," HLT ")!==false) {
			// Find anything to the left of the instruction as a line label
			$left = trim(substr($row,0,stripos($row," HLT ")));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "000";
			$memLocation++;
		}

		if (stristr($row," ADD ")!==false) {
			// Find anything to the left of the instruction as a line label
			// Find anything to the right to set the memory location this will map to
			$left = trim(substr($row,0,stripos($row," ADD ")));
			$right = trim(substr($row,stripos($row," ADD ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			//Stores the string to the right of the instruction, padded by "%" to the right of the instruction
			$intermediate[] = "1%$right%";
			$memLocation++;
		}

		if (stristr($row," SUB ")!==false) {
			$left = trim(substr($row,0,stripos($row," SUB ")));
			$right = trim(substr($row,stripos($row," SUB ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "2%$right%";
			$memLocation++;
		}

		if (stristr($row," STA ")!==false) {
			$left = trim(substr($row,0,stripos($row," STA ")));
			$right = trim(substr($row,stripos($row," STA ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "3%$right%";
			$memLocation++;
		}

		if (stristr($row," LDA ")!==false) {
			$left = trim(substr($row,0,stripos($row," LDA ")));
			$right = trim(substr($row,stripos($row," LDA ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "5%$right%";
			$memLocation++;
		}

		if (stristr($row," BRA ")!==false) {
			$left = trim(substr($row,0,stripos($row," BRA ")));
			$right = trim(substr($row,stripos($row," BRA ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "6%$right%";
			$memLocation++;
		}

		if (stristr($row," BRZ ")!==false) {
			$left = trim(substr($row,0,stripos($row," BRZ ")));
			$right = trim(substr($row,stripos($row," BRZ ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "7%$right%";
			$memLocation++;
		}

		if (stristr($row," BRP ")!==false) {
			$left = trim(substr($row,0,stripos($row," BRP ")));
			$right = trim(substr($row,stripos($row," BRP ")+5,strlen($row)));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "8%$right%";
			$memLocation++;
		}

		if (stristr($row," INP ")!==false) {
			$left = trim(substr($row,0,stripos($row," INP ")));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "901";
			$memLocation++;

		}

		if (stristr($row," OUT ")!==false) {
			$left = trim(substr($row,0,stripos($row," OUT ")));
			if ($left!="") {
				$memoryMapper[$left] = $memLocation;
			}

			$intermediate[] = "902";
			$memLocation++;
		}

		if (stristr($row," DAT ")!==false) {
			$left = trim(substr($row,0,stripos($row," DAT ")));
			$right = trim(substr($row,stripos($row," DAT ")+5,strlen($row)));
			if ($left!="") {
				// This will make a limitation where the line labels and vars cannot overlap
				$memoryMapper[$left] = $memLocation;
				$intermediate[] = str_pad($right,3,"0",STR_PAD_LEFT);
			}
			$memLocation++;
		}
	}

	/*
		Uncomment these to see the memory mapper, which will contain label to memory location (without leading zeros).
		Also, it will show the intermediate code before symbol replacement, as a start to outputting machine code.
	*/
	//print_r($memoryMapper);
	//print_r($intermediate);

	
	// Replace labels / symbols (vars & line labels) with memory locations
	foreach ($intermediate as $row) {
		foreach ($memoryMapper as $k => $v) {
			// Case insensitive, I'm fine with introducing this limitation
			$row = str_ireplace("%$k%",str_pad($v,2,"0",STR_PAD_LEFT),$row);
		}
		$out[] = $row;
	}
	return $out;
}