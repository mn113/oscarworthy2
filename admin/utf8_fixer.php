<?php
require_once('../config.php');


function detect($str) {
	for ($index = 0; $index < strlen($str); $index++)  {
	 
	    //get the ASCII value
	    $byte = ord($str[$index]);
	 
	    if ($byte < 32 && $byte != 10)  {
	        $length = 1;
		    echo '['.$byte.']';
	        echo "weird char: ".substr($str, $index, $length)." $str<br> ";			
		}
	    elseif ($byte <= 127)  {
	        $length = 1;
	        //echo "1-byte char: ".substr($str, $index, $length)." <br> ";
		}
	    else if ($byte >= 194 && $byte <= 223)  {
	        $length = 2;
	        echo "2-byte char: ".substr($str, $index, $length)." <br> ";
		}
	    else if ($byte >= 224 && $byte <= 239)  {
	        $length = 3;
	        echo "3-byte char: ".substr($str, $index, $length)." <br> ";
		}
	    else if ($byte >= 240 && $byte <= 244)  {
	        $length = 4;
	        echo "4-byte char: ".substr($str, $index, $length)." <br> ";
	   	}
	}
}

function convert($str) {
	$str2 = '';
	for ($index = 0; $index < strlen($str); $index++)  {
	 	
	    //get the ASCII value
	    $byte = ord($str[$index]);
	 
	    if ($byte >= 32)  {	// Normal ASCII char, echo it back
	        $str2 .= substr($str, $index, 1);			
		}
		else {				// Funny char, replace it
			switch($byte) {
				case 3:		$str2 .= 'É'; break;
				case 7:		$str2 .= 'á'; break;
				case 14:	$str2 .= 'é'; break;
				case 15:	$str2 .= 'è'; break;
				case 17:	$str2 .= 'ë'; break;
				case 18:	$str2 .= 'í'; break;
				case 19:	$str2 .= 'ì'; break;
				case 22:	$str2 .= 'ñ'; break;
				case 23:	$str2 .= 'ó'; break;
				case 25:	$str2 .= 'ô'; break;
				case 26:	$str2 .= 'ö'; break;
				case 29:	$str2 .= 'a'; break;
				case 31:	$str2 .= 'ü'; break;
			}
		}
	}
	return $str2;
}

$sth = $dbh->query("SELECT award_id, film, person FROM awards_temp ".
//					WHERE person LIKE 'pedro%' OR person LIKE 'lasse%' OR person LIKE '%zellweger' OR person LIKE '%cruz' 
					"LIMIT 5000");
$rows = $sth->fetchAll();

foreach ($rows as $row) {
	extract($row);	// makes variables of all db fields
	FB::log($film.' ::: '.$person);
//	echo $award_id.' '.$film.' '.$person.'<br>';	
	echo "UPDATE awards_temp SET film = '".convert($film)."', person = '".convert($person)."' WHERE award_id = ".$award_id." LIMIT 1;";
	echo "<br>";
//	echo detect($person);
//	echo detect($film);
//	echo '<br>';

}

echo 'done';