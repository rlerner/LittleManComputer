<?php
class Output {
	private $data = [];

	public function set($data) {
		$this->data[] = $data;
	}

	public function get() {
		return $this->data;
	}
}