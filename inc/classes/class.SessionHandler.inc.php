<?php 

class SessionHandler {	// This class is a Singleton and represents a user's session. Instantiated on every page.


    private static $instanceOf;

    public static function getInstance() {
		if (!self::$instanceOf) {
			self::$instanceOf = new SessionHandler();
		}
		return self::$instanceOf;
    }

	function __construct() {
		// Autoload session vars into object:
		$this->feedbackVotes = isset($_SESSION['feedbackVotes']) ? $_SESSION['feedbackVotes'] : array();
	}

	function __destruct() {
		// Autosave object vars into session:
		$_SESSION['feedbackVotes'] = $this->feedbackVotes;
	}


	protected $feedbackVotes = array();

	function setFeedbackVotes($cid, $vote) {
		$this->feedbackVotes[$cid] = $vote;
	}

	function getFeedbackVotes() {
		return $this->feedbackVotes;
	}
}