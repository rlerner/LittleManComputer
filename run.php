<?php
// R.Lerner Little Man Computer (LMC) 2022/07/20
require_once "lib/LMC.class.php";
require_once "lib/Output.class.php";
require_once "lib/Keyboard.class.php";
require_once "lib/RAM.class.php";
require_once "lib/Register.class.php";
require_once "lib/Flag.class.php";

echo "LittleManComputer Runner v0.0.1 / R.Lerner / 2022/07/26\n";

if (!isset($argv[1])) {
	die("No input file. Run with `{$argv[0]} filename.lmc`\n");
}

$fileName = escapeshellcmd($argv[1]);

if (!file_exists($fileName)) {
	die("File '$fileName' not found.\n");
}
$file = file_get_contents($argv[1]);
$program = explode("\n",$file);

$ram = new RAM;
$output = new Output;

// Load program into RAM
$loc = 0;
foreach ($program as $k => $v) {
	$ram->set($k,$v);
}

$pc = new Register();
$accumulator = new Register();

$lmc = new LMC($ram, new Input, $output, new Register, new Register, new Register, new Flag, true);
$lmc->run();
$x = $output->get();

echo "\n----------\n";
if (count($x)==1) {
	echo $x[0];
} else {
	print_r($x);
}
echo "\n\n";


//todo: bobmult2.lmc (0 times anything is not zero) -- added a few BRZs to the inputs

