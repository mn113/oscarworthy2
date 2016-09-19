<?php
require_once('config.php');

$fred = new User($dbh, 'Fred', 'pass', 'pppp29pp@yahoo.com', 'realname');

$eml = new Email($fred);
$eml->setBody('welcome');
$eml->sendThis();

echo 'ok';
