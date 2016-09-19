<?php

$bio = "
From Wikipedia, the free encyclopedia.

Jason Patric (born June 17, 1966) is an American film, television and stage actor. He may be best-known for his roles in the films The Lost Boys, Sleepers, Your Friends & Neighbors, Narc, The Losers and Speed 2: Cruise Control. His father is the actor/playwright Jason Miller. Patric is the maternal grandson of Jackie Gleason.

Description above from the Wikipedia article Jason Patric, licensed under CC-BY-SA, full list of contributors on Wikipedia
";

echo "<PRE>";

echo $bio."\n\n\n\n";

echo stripWikiText($bio);


function stripWikiText($bio) {
	$ex1 = "From Wikipedia, the free encyclopedia.";
	$ex2 = "Description above from the Wikipedia article";
	
	// Find ex1 index:
	$idx1 = strpos($bio, $ex1);	
	// Trim start:
	if ($idx1) {
		$bio = substr($bio, $idx1 + strlen($ex1));
	}
	
	// Find ex2 index:
	$idx2 = strpos($bio, $ex2);
	// Trim end:
	if ($idx2) {
		$bio = substr($bio, 0, $idx2);
	}
	
	return trim($bio);
}