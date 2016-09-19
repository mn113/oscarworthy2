<?php

$fid = 2343;
$pid = 1209;
$job = 'Actor';

$hash = substr(md5($fid.$job.$pid),0,8);

echo $hash;