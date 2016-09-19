<?php
/**
 * When a page will display stars (i.e. film or person page), this helper is instantiated once early on
 * to retrieve the db average ratings and member votes. It's all plonked in $_SESSION['votes']
 * for the static method displayStars() to access in the loop.
 */
class StarHelper {

	public $resource_id;
	public $uid = null;
	public $page;	// film or person
	public $id;		// fid or pid
	public $db_col;

	protected $dbh;
	
	
	function __construct($dbh, $page, $id) {
		$this->page = $page;
		$this->id = $id;	
		$this->db_col = $this->page.'_id';

		// User_id for vote retrieval:
		if (User::isLogged()) $this->uid = $_SESSION['uid'];
				
		// Dependency Injection:
		$this->dbh = $dbh;
	}


	function __destruct() {
		// Cleanup:
		$this->dbh = null;
	}


	/**
	* Retrieve the votes cast by the logged-in member on this page.
	*/
	function getMemberRatings() {
		if ($this->uid) {
			$sth = $this->dbh->prepare("SELECT r.unique_hash, v.rating 
										FROM votes AS v
										INNER JOIN roles AS r
										ON r.unique_hash = v.role_hash
										WHERE v.uid = ? AND r.".$this->db_col." = ?");
			$sth->bindParam(1, $this->uid, PDO::PARAM_INT);
			$sth->bindParam(2, $this->id, PDO::PARAM_INT);
	
			try {
				$sth->execute();
				if ($sth->rowCount() > 0) {
					$rows = $sth->fetchAll();
					foreach ($rows as $row) {
						extract($row, EXTR_PREFIX_ALL, 'r');
						// Put fetched results in the $_SESSION:
						$_SESSION['votes'][$r_unique_hash]['memvote'] = $r_rating / 2;	// @RATING HALVED
					}
				}
				return true;
			}
			catch (PDOException $e) {
				FB::log($e);
				return false;
			}
		}
	}


	/**
	* Retrieve average scores for roles on this page.
	*/
	function getAvgRatings() {		
		// Fetch all scores:
		$sth = $this->dbh->prepare("SELECT unique_hash, mem_score, mem_votes, guest_score, guest_votes FROM roles WHERE ".$this->db_col." = ?");
		$sth->bindParam(1, $this->id, PDO::PARAM_INT);
		try {
			$sth->execute();
			if ($sth->rowCount() > 0) {
				$rows = $sth->fetchAll();
				foreach ($rows as $row) {
					extract($row, EXTR_PREFIX_ALL, 'r');
					// Do math:
					$r_guest_rating = $r_guest_votes ? $r_guest_score / $r_guest_votes : null;
					$r_mem_rating = $r_mem_votes ? $r_mem_score / $r_mem_votes : null;

					// Vote weighting:
					$r_avg_rating = $r_mem_rating + ($r_guest_rating * 0.1);

					// Put fetched results in the $_SESSION:
					$_SESSION['votes'][$r_unique_hash]['avgrate'] = $r_avg_rating / 2;	// @RATING HALVED
				}
			}
		}
		catch (PDOException $e) {
			FB::log($e);
		}			
	}


	/**
	* Retrieve the score & votes for a known role. Prepare star rater.	// TO BE DEPRECATED
	*/
	static function displayStars($hash) {
		// Look in the session for the votes we pulled earlier:
		if (isset($_SESSION['votes'][$hash])) {
			$memvote = @$_SESSION['votes'][$hash]['memvote']; // USING @ TO SUPPRESS CONSTANT NOTICES "Undefined index: memvote"
			$avgrate = $_SESSION['votes'][$hash]['avgrate'];
		}
		else {
			$avgrate = $memvote = 0;
		}
	
		// Build the RateIt HTML:
		if ($memvote) {
			$stars  = "<div id='$hash' class='rateit mem'";
			$stars .= " data-rateit-resetable='false'";
//			$stars .= " data-rateit-ispreset='true' data-rateit-value='$memvote'";
//			$stars .= " data-rateit-readonly='true'";
			$stars .= "></div>";
 		}
		elseif ($avgrate) {
			$stars  = "<div id='$hash' class='rateit avg'";
			$stars .= " data-rateit-resetable='false'";
//			$stars .= " data-rateit-ispreset='true' data-rateit-value='$avgrate'";
			$stars .= "></div>";
		}
		else {
			$stars  = "<div id='$hash' class='rateit'";
			$stars .= " data-rateit-resetable='false'";
			$stars .= "></div>";
		}
		return $stars;
	}
}