<?php

class Bio {		// static class


	/**
	 * Cleans the generic Wikipedia bio of unwanted text.
	 */
	static function stripWikiText($bio) {
	
		$unwanted = array("From Wikipedia, the free encyclopedia.",
						  "From Wikipedia, the free encyclopedia",
						  "Description above from the Wikipedia article",
						  ", full list of contributors on Wikipedia."
					);

		foreach ($unwanted as $crap) {
			$bio = preg_replace('/'.$crap.'/', '', $bio);
		}
		
		// Shorten:
		if (strlen($bio) > 500) {
			$laststop = strpos($bio, '. ', 500);
			$bio = substr($bio, 0, $laststop+1);
		}
		
		return trim($bio);
	}
	
}
