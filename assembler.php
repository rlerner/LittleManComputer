<?php
// LittleManComputer Assembler - (c)2022 Robert Lerner, All Rights Reserved
require_once "lib/LMCAssembler.class.php";
echo "LittleManComputer Assembler | (c)2022 Robert Lerner | v20220726233200\n";
if (!isset($argv[1])) {
	die("No input file. Run with `{$argv[0]} filename`\n");
}
$fileName = escapeshellcmd($argv[1]);
if (!file_exists($fileName)) {
	die("File '$fileName' not found.\n");
}

$file = file_get_contents($fileName);

$assembler = new LMCAssembler();
//$assembler->debug = true;
$output = $assembler->assemble($file);

if (count($assembler->warnings)!=0) {
	foreach ($assembler->warnings as $v) {
		echo "  WARNING: $v\n";
	}
}

file_put_contents(str_ireplace(".asm","",$argv[1]) . ".lmc",$output);
echo "Assembly Complete. Outputting Symbol Table...";
$sym = $assembler->outputSymbolTable();
file_put_contents(str_ireplace(".asm","",$argv[1]) . ".sym",$sym);
echo "Done.\n\n";