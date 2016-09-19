<?php 

class Singleton {

	private $instanceOf;


	// Nobody can call this:
	private function __construct() {
		if (!isset(self::$instanceOf)) {
			$self::instanceOf = new Singleton();
		}
		return self::$instanceOf;
	}


	// But I can!
	public function getInstance() {
		self::__construct;	
	}
}