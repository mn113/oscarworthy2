<?php

class Feedback {	// Static class

	static function getComments() {
		global $dbh;
		$sth = $dbh->prepare("SELECT * FROM feedback WHERE visible = 1 ORDER BY comment_id ASC");
//		$sth->setFetchMode(PDO::FETCH_ASSOC);
		try {
			$sth->execute();
			if ($sth->rowCount() > 0) {
				$comments = $sth->fetchAll();
				$sess = SessionHandler::getInstance();

				// Put comments in threaded order using trickery:
				$i = 1;
				foreach ($comments as $c) {
					// Root-level comments:
					if ($c['parent_id'] == 0) {
						$results[$c['comment_id']*1000] = $c;
					}
					// Replies:
					else {
						$new_index = $c['parent_id']*1000 + $i;
						$results[$new_index] = $c;
						$i++;
					}
				}
				ksort($results);

				// Display the comments:
				foreach ($results as $row) {
					extract($row, EXTR_PREFIX_ALL, 'c');
					$comm = new Comment($dbh, $c_cbody, $c_username, $c_uid, $c_parent_id, $c_cdate, $c_upvotes, $c_flags, $c_visible);
					$comm->display();
				}
			}
			else {
				Messages::create('error', "An error occurred.");
			}
		}
		catch (PDOException $e) {
			FB::log($e);
			Messages::create('error', "An error occurred.");
		}
	}

} // end class Feedback
