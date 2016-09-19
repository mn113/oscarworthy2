<?php
/**
* Static class which exclusively manipulates $_SESSION['messages'].
*/
class Messages {

	/**
	* Create one message in $_SESSION.
	*/
	static function create($class, $text, $msg_id = null, $autofade = false, $sticky = false) {
		// Make a unique id:
		if (!$msg_id) $msg_id = substr(microtime(), 4, 4);

		$msg = array(
			'class'		=>	$class,
			'text'		=>	$text,
			'autofade'	=>	$autofade,
			'sticky'	=>	$sticky
		);
		// Store:
		$_SESSION['messages'][$msg_id] = $msg;
	}


	/**
	* Display all messages in $_SESSION on the page.
	*/
	static function buildHtml($msg_id, array $msg) {
		extract($msg);
		$html = "<div id='message".$msg_id."' class='message ".$class;
		if ($autofade) $html .= " autofade";
		$html .= "'>".
				 $text.
				 "<span class='close_button' /></div>";
		return $html;		
	}


	/**
	* Display all messages in $_SESSION on the page.
	*/
	static function displayAll() {
		if (isset($_SESSION['messages'])) {
			foreach ($_SESSION['messages'] as $msg_id => $msg) {
				$html = self::buildHtml($msg_id, $msg);

//				$html = "<div id='message".$msg_id."' class='message ".$msg['class'];
//				if ($msg['autofade']) $html .= " autofade";
//				$html .= "'>".$msg['text']."<span class='close_button' /></div>";
//				echo $html;

				// Remove from $_SESSION:
				if (!$msg['sticky']) self::deleteById($msg_id);
				echo $html;
			}
		}
	}


	/**
	* Return one message immediately for js to show.
	*/
	static function displayById($msg_id) {
		if (isset($_SESSION['messages'][$msg_id])) {
			$msg = $_SESSION['messages'][$msg_id]; 
			$html = self::buildHtml($msg_id, $msg);

//			$html = "<div id='message".$msg_id."' class='message ".$msg['class'];
//			if ($msg['autofade']) $html .= " autofade";
//			$html .= "'>".$msg['text']."<span class='close_button' /></div>";
//			return $html;

			// Remove from $_SESSION:
			if (!$msg['sticky']) self::deleteById($msg_id);
			// Return to calling AJAX:
			return $html;
		}
	}


	/**
	* Delete all messages in $_SESSION. Except stickies.	// WHEN DO WE NEED THIS IF CLICKING NOW DELETES A MSG?
	*/
	static function deleteAll() {
		unset($_SESSION['messages']);
	}	


	/**
	* Delete one message by id.
	*/
	static function deleteById($msg_id) {
		unset($_SESSION['messages'][$msg_id]);
	}

} // end class Messages