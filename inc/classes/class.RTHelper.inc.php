<?php

class RTHelper extends RottenTomatoesApi {


	protected $dbh;
	protected $tmdb;
	

	function __construct($dbh, $tmdb, $countrycode) {
		parent::__construct(RT_APIKEY, 10, '1.0', $countrycode);

		// Dependency injection:
		$this->dbh = $dbh;
		$this->tmdb = $tmdb;
	}


	function displayList(array $list, $heading = '') {
		$html  = "<div class='listbox'>";
		$html .= "<h3>".$heading."</h3>\r\n";
		$html .= '<ol>';
		$i = 1;
		foreach ($list['movies'] as $rtm) {
			// Construct IMDb id:
			$imdb_id = 'tt'.str_pad($rtm['alternate_ids']['imdb'], 7, '0', STR_PAD_LEFT);
			// TMDb lookup by IMDb id:
			if ($json = $this->tmdb->getMovie($imdb_id, TMDB::IMDB)) {
				$tf = objectToArray(json_decode($json));
				FB::log($tf);
				
				if (!empty($tf[0]['name'])) {
					// Make row:
					$html .= "<li class='cf'>".$i.". <a href='/film/".$tf[0]['id']."'>".$tf[0]['name']."</a> (".year($tf[0]['released']).")";
					// Find poster thumbnail:
					$thumb = BLANK_POSTER;
					foreach ($tf[0]['posters'] as $p) {
						if ($p['image']['size'] == 'thumb') {
							$thumb = $p['image']['url'];
							break;
						}
					}
					$html .= "<img src=".$thumb." class='thumb' /></li>\r\n";

					// Count out 10 rows:
					$i++;
					if ($i > 10) break;
				}
			}
		}
		$html .= '</ol></div>';
		echo $html;
	}

}
