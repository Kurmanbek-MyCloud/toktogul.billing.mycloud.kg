<?php

class Jeng {

	private $file;

	public function __construct($file) {

		$this->file = $file;
	}

	public function log($message) {

		$text = date('Y-m-d H:i:s').' :: '.$message."\n";
		$fopen = fopen($this->file,'a');
		fwrite($fopen, $text);
		fclose($fopen);
	}
}