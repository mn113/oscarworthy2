<?php

/**
 * Converts/simplifies an object to an array.
 */
function objectToArray($object) {
	if (!is_object($object) && !is_array($object)) {
	    return $object;
	}
	if (is_object($object)){
	    $object = get_object_vars($object);
	}
	return array_map('objectToArray', $object);
}


/**
 * Converts YYYY-MM-DD to YYYY.
 */
function year($release_date) {
	return substr($release_date,0,4);	// LAZY
}


/**
 * Serializes lists such as genres, countries, studios, languages for db write.
 */
function quickSerialize($arr, $idx = 'name') {
	if (!is_array($arr)) return false;
	
	$list = array();
	foreach ($arr as $item) {
		$list[] = $item[$idx];
	}
	$str = implode(', ', $list);
	return $str;
}


/**
 * Generates the unique hash for a role.
 */
function hashRole($fid, $job, $pid) {
	return substr(md5($fid.$job.$pid),0,8);
}


/**
 * Sort an array by length of values (desc).
 */
function sortByLength($a,$b){
	if ($a == $b) return 0;
	return (strlen($a) > strlen($b) ? -1 : 1);
}
