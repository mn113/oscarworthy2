<?
class GeoLoc {
	
	# Function to parse data obtained from HOSTIP LOOKUP SERVICE, coded by Don Cullen (www.doncullen.net)
	#
	# example usage: $geoiparray = getInfoFromIP('8.8.8.8');
	#
	# Returns an array containing data. available fields: city, state, country, countryabbrev
	#
	function getInfoFromIP($theip){
	    if(!$theip) return false;    # Missing parameter
	
	    # Pull the XML
	    $url = 'http://api.hostip.info/?ip='.$theip;
	    $xml = simplexml_load_file($url);
	
	    # Parse the data and store into array
	    $citystate = explode(", ", $xml->children('gml', true)->featureMember->children()->Hostip->children('gml', true)->name);
	    $result['city'] = $citystate[0];
	    $result['state'] = @$citystate[1];
	    $result['country'] = $xml->children('gml', true)->featureMember->children()->Hostip->countryName;
	    $result['cc'] = $xml->children('gml', true)->featureMember->children()->Hostip->countryAbbrev;
	    
	    return $result;
	}
	
}
