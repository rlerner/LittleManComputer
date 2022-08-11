<?php
class Flag {
	private $state = null;
	public function set(bool $val):void {
		$this->state = $val;
	}
	public function get():bool {
		if ($this->state===null) {
			throw new Exception("Flag not initalized");
		}
		return $this->state;
	}
}