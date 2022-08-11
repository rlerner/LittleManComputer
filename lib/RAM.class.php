<?php
class RAM {
	private $ram = [];

	public function set(int $location,int $value) {
		if ($location<0 || $location>99) {
			throw new Exception("Out of Bounds");
		}
		if ($value<0 || $value>999) {
			throw new Exception("Overflow at $location, value $value.");
		}
		$this->ram[$location] = $value;
		return true;
	}

	public function get(int $location) {
		if ($location<0 || $location>99) {
			throw new Exception("Out of Bounds");
		}
		if (!isset($this->ram[$location])) {
			$this->ram[$location] = 0;
		}
		return $this->ram[$location];
	}

	//Dumps cells that are currently set, if they were never set during program load or execution, it will not be returned.
	public function dump() {
		$image = "";
		foreach ($this->ram as $k => $v) {
			$image .= str_pad($k,3,"0",STR_PAD_LEFT) . ":" . str_pad($v,3,"0",STR_PAD_LEFT) . "\n";
		}
		return $image;
	}

	//Imports image from dump above
	public function import($image) {
		$image = explode("\n",$image);
		foreach($image as $v) {
			$write = explode(":",$v);
			$this->set($write[0],$write[1]);
		}
		return true;
	}
}