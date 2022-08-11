<?php
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

/*
# ex.

$inputs = [
	'6'
	,'100'
];
$input = new INPUT();
$input->put($inputs);

*/