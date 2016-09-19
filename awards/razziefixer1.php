<?php

echo "<pre>";

$fh = fopen('razzies.txt', 'r');

while ($line = fgets($fh)) {
	$pre = substr($line, 0, 4);
	if (is_numeric($pre)) {
		$year = $pre;
		$line = substr($line, 5);
	}
	echo $year."::".$line;
}