<?php
require_once('../config.php');


// AJAX-call triggers various functions:

if (isset($_POST['msg_id']) && is_numeric($_POST['msg_id'])) {
	$msg_id = (int) $_POST['msg_id'];

	if ($_POST['action'] == 'display') {
		// Return a message to js to show:
		$msg = Messages::displayById($msg_id);
		echo $msg;
	}
	elseif ($_POST['action'] == 'delete') {
		// Delete a message by timestamp when closed:
		Messages::deleteById($msg_id);
		echo $msg_id.' deleted.';
	}
	
}

