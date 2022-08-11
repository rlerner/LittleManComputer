<?php
class Register {
	private $val = null;

	public function set(int $value) {
		if ($value<0 || $value>999) {
			throw new Exception("Register Out of Bounds ($value)");
		}
		$this->val = $value;
	}

	public function get(): int {
		if ($this->val===null) {
			throw new Exception("Undefined initial register value");
		}
		return $this->val;
	}
}