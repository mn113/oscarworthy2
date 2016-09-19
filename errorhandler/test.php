<?php
	require('ErrorHandler.php');
	
	define('FATAL', E_USER_ERROR);
	define('ERROR', E_USER_WARNING);
	define('WARNING', E_USER_NOTICE);

	$errorHandler = new ErrorHandler(false);

	trigger_error('yipes', FATAL);
?>