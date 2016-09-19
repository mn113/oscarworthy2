<?php

class ErrorHandler {

    private $debug = true;

    public function __construct($debug = true) {
        $this->debug = $debug;
        set_error_handler(array($this, 'handleError'));
    }

    public function handleError($errorType, $errorString, $errorFile, $errorLine) {	
        switch ($errorType) {
            case FATAL:
	            switch ($this->debug) {
					case true:
	                    die('Sadly an error has occured!');
	                case false:
	                    $this->printError("<pre style='color:red'><b>FATAL</b> [T: $errorType] [L: $errorLine] [F: $errorFile]<br/>$errorString<br /></pre>");
	                    die;
	            }
            case ERROR:
	            $this->printError("<pre style='color:blue'><b>ERROR</b> [T: $errorType] [L: $errorLine] [F: $errorFile]<br/>$errorString<br /></pre>");
	            break;
            case WARNING:
	            $this->printError("<pre style='color:green'><b>WARNING</b> [T: $errorType] [L: $errorLine] [F: $errorFile]<br/>$errorString<br /></pre>");
	            break;
        }
    }
    
    public function printError($str) {
		$msg['class'] = 'error';
		$msg['text'] = $str;
		$_SESSION['messages'][] = $msg;
    }
} 
