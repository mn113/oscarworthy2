<?php

class Codes {


	function generateActivationCode() {
		return substr(md5(microtime()), 0, 16);
	}


	function generateResetPassCode() {
		return substr(md5(microtime()), 0, 16);
	}


	function hashRole() {}
	

}