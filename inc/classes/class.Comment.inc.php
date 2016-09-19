<?php

class Comment {

	public $cid;
	public $parent_id;
	public $uid;
	public $username;
	public $cbody;
	public $cdate;
	public $upvotes;
	public $flags;
	public $visible;
	
	protected $dbh;
	

	function __construct($dbh, $cbody, $username, $uid = 0, $parent_id = 0, $cdate = '', $upvotes = '', $flags = '', $visible = 1) {
		$this->dbh = $dbh;
		$this->cbody = $cbody;
		$this->username = $username;
		$this->uid = $uid;
		$this->parent_id = $parent_id;
		$this->cdate = $cdate;
		$this->upvotes = $upvotes;
		$this->flags = $flags;
		$this->visible = $visible;
	}


	/*
	 * Insert the comment into the db.
	 */
	function post() {
		// Check for a dupe against last 3 comments:
		$sth = $this->dbh->prepare("SELECT * FROM feedback ORDER BY comment_id DESC LIMIT 3");
		$sth->execute();
		foreach ($sth->fetchAll() as $row) {
			if ($row['username'] == $this->username && $row['cbody'] == $this->cbody) return false;
		}		
	
		// Not a dupe, insert it:
		$sth = $this->dbh->prepare("INSERT INTO feedback (parent_id, username, uid, cbody, cdate) VALUES (?, ?, ?, ?, NOW())");
		$sth->bindParam(1, $this->parent_id);
		$sth->bindParam(2, $this->username);
		$sth->bindParam(3, $this->uid);
		$sth->bindParam(4, $this->cbody);
		try {
			$sth->execute();
			if ($sth->rowCount() == 1) {
				Messages::create('success', "Thanks for posting!");
				return true;
			}
			else {
				Messages::create('error', "An error occurred and your comment was not posted.");
				return false;
			}
		}
		catch (PDOException $e) {
			FB::log($e);
			return false;
		}
	}


	/*
	 * Change one column on this comment's record.
	 */
	function upvote() {
		$q = "UPDATE feedback SET upvotes = upvotes + 1 WHERE comment_id = ? LIMIT 1";
		return $this->update($q);
	}


	/*
	 * Change one column on this comment's record.
	 */
	function flag() {
		$q = "UPDATE feedback SET flags = flags + 1 WHERE comment_id = ? LIMIT 1";
		return $this->update($q);
	}
	
	
	/*
	 * Change one column on this comment's record.
	 */
	function hide() {
		$q = "UPDATE feedback SET visible = 0 WHERE comment_id = ? LIMIT 1";
		return $this->update($q);
	}


	/*
	 * Common "touch-one-row" database routine.
	 */
	function update($q) {
		$sth = $this->dbh->prepare($q);
		$sth->bindParam(1, $this->cid, PDO::PARAM_INT);
	
		try {
			$sth->execute();
			if ($sth->rowCount() == 1) {
				$sess = SessionHandler::getInstance();
				$sess->setFeedbackVotes($cid, $action);
				return true;
			}
			else {
				return false;
			}
		}
		catch (PDOException $e) {
			FB::log($e);
			return false;
		}
	}


	/*
	 * Render one comment and all its accoutrements.
	 */
	function display() {
		// Skip blanks:
		if ($this->cbody != '') {
			// Prepare comment html:
			$html  = "<div class='comment cf";
			// Is it a reply?
			if ($this->parent_id != 0) {
				$html .= " reply";
			}
			if ($this->uid == 1) {
				$html .= " owner";
			}
			$html .= "' id='comment".$this->cid.
					 "' score='".$this->upvotes."' flags='".$this->flags."'>".
					 "<p class='heading'><span class='author'>".$this->username."</span> on ".$this->cdate."</p>".
					 "<div class='cbody'>".$this->cbody.
					 "<em>".$this->upvotes."</em>".
					 "<span class='reply_btn'>Reply</span>";

			// See if we should display voting buttons:
			$sess = SessionHandler::getInstance();
			if (User::isLogged() && !array_key_exists($this->cid, $sess->getFeedbackVotes())) {
				$html .= "<span class='upvote_btn'>Upvote</span>".
						 "<span class='flag_btn'>Flag</span>";
			}

			// Delete icon for admin:
			if (User::isAdmin()) {
				$html .= "<a href='#' class='delete'></a>";
			}
			$html .= "</div></div>";

			echo $html;
		}
	}


} // end class Comment
