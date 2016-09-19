<?php

class Box {		// represents a box/div/module on the page

	public $html_id;
	public $html_classes;
	public $heading;
	public $contents;

	function __construct($heading = null, $html_id, $html_classes) {
	
	}
	
	
	function displayWith($content) {
	
	}
}



// call from page:

$box = new Box('heading', 'myid', 'class1 class2');
$box->displayWith($film->displayAwards());
