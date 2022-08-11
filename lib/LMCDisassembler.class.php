<?php
/*
 -- What happens when a memory cell contains an operable instruction?
    - Will potentially need a way to determine last instruction, will be hard since uninitalized memory and HLTs = 000

*/

class LMCDisassembler {

	public function disassemble($program) {
		$program = explode("\n",$program);
		$varCounter = 0;
		$labelCounter = 0;
		$opcodes = [
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

		//Find anything that refers to a memory location
		foreach ($program as $k => $v) {
			$instruction = substr($v,0,1);

			switch($instruction) {
				case 0:
					// Don't replace it with HLT codes here since memory fields will need the 000 in place.
					break;
				case 1:
				case 2:
				case 3:
				case 5:
					// Memory Locations
					$memory[] = substr($v,1,2);
					$program[$k] = $opcodes[$instruction] . " %MEMORY_PLACEHOLDER_" . substr($v,1,2) . "%";
				break;
				case 6:
				case 7:
				case 8:
					// Labels
					$lbl = ltrim(substr($v,1,2),"0");

					$labels[] = $lbl;
					$program[$k] = $opcodes[$instruction] . " %LABEL_PLACEHOLDER_$lbl%";
				break;
				case 9:
					if ($v=="901") {
						$program[$k] = "INP";	
					}
					if ($v=="902") {
						$program[$k] = "OUT";
					}
				break;
					
			}
		}

		$labels = array_unique($labels); // Multiple lines may reference one label
		$memory = array_unique($memory); // Multiple lines may reference one memory location

		//print_r($memory);
		//print_r($labels);

		$memoryCounter = 0;
		foreach ($memory as $v) {
			$program[$v] = "MEMORY_$memoryCounter DAT {$program[$v]}";
			$memoryCollection["MEMORY_PLACEHOLDER_$v"] = "MEMORY_$memoryCounter";
			$memoryCounter++;
		}

		// Update program with memory
		foreach ($memoryCollection as $memoryReference => $mem) {
			foreach ($program as $id => $instruction) {
				$program[$id] = str_replace("%$memoryReference%",$mem,$program[$id]);
			}
		}


		$labelCounter = 0;
		foreach ($labels as $v) {
			$program[$v] = "LABEL_$labelCounter " . $program[$v]; // Add labels to the program
			$labelCollection["LABEL_PLACEHOLDER_$v"] = "LABEL_$labelCounter"; // Map associations of instruction cells
			$labelCounter++;
		}


		// Update program with new jump/branch TO labels
		foreach ($labelCollection as $labelReference => $label) {
			foreach ($program as $id => $instruction) {
				$program[$id] = str_replace("%$labelReference%",$label,$program[$id]);
			}
		}

		// Replace exact matches for "000" with "HLT"
		foreach ($program as $k=>$v) {
			if ($v=="000") {
				$program[$k] = "HLT";
			}
		}
		return implode("\n",$program);
	}
}