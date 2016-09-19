<?php

class Cache {

	private $cachefile;
	private $cachetime;	// Seconds


	function __construct($time = 30, array $params = null) {	// PASS IN PARAMS SPECIFIC TO PAGE?
		// Get rid:
		global $base;
//		global $qs;
		global $geo;

		$extras = $params ? implode('_', $params) : '';

		// Filename ought to include page, query string, country_abbrev, date(?)
		$this->cachefile = '../cache/'.$base.'_'.$extras.'.html';	//.$geo['cc']
		$this->cachetime = $time;
	}


	function check() {
		// Serve from the cache if it is younger than $cachetime
		if (file_exists($this->cachefile) && (time() - $this->cachetime < filemtime($this->cachefile))) {
			$page = file_get_contents($this->cachefile);
			$page .= "<!-- Cached ".date('jS F Y H:i', filemtime($this->cachefile))." -->";
			$page .= "<img src='/img/icons/disk.png' class='fromcache' />";
			exit($page);
		}
		ob_start(); // No cache retrieved, start the output buffer
	}
	
	
	function create() {
		if (!file_exists($this->cachefile)) touch($this->cachefile);	// create cachefile
		if ($fh = fopen($this->cachefile, 'w')) {
			fwrite($fh, ob_get_contents()); // save the contents of output buffer to the file
			fclose($fh);
		}
		ob_end_flush(); // Send the buffer contents to the browser
	}

}
