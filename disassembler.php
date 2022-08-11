<?php
// LittleManComputer Disassembler - (c)2022 Robert Lerner, All Rights Reserved
require_once "lib/LMCDisassembler.class.php";
echo "LittleManComputer Disassembler | (c)2022 Robert Lerner | v20220726233200\n";
if (!isset($argv[1])) {
	die("No input file. Run with `{$argv[0]} filename`\n");
}
$fileName = escapeshellcmd($argv[1]);
if (!file_exists($fileName)) {
	die("File '$fileName' not found.\n");
}

$file = file_get_contents($fileName);
$disassembler = new LMCDisassembler();
$out = $disassembler->disassemble($file);

file_put_contents(str_ireplace(".lmc","",$fileName) . ".disassembled.asm",$out);
echo "Done\n";