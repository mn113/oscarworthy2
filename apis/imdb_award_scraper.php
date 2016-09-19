<?php
include('phpQuery-onefile.php');

// Setup
$imdb_id = 'tt0181865';
$url = "http://www.imdb.com/$imdb_id/awards";

// Retrieval
//$raw = file_get_contents($url);	// worked, now 404

// Local
$filename = "$imdb_id.html";
//fwrite(fopen($filename, 'w'), $raw);
$raw = file_get_contents($filename);

// Cleaning
$newlines = array("\t","\n","\r","\x20\x20","\0","\x0B");
$content = str_replace($newlines, "", html_entity_decode($raw));

// Extract table
$start = strpos($content,'<th>Year</th>');
$headoff = substr($content, $start);
$end = strpos($headoff,'</table>') + 8;
$table = "<table><tr>".substr($headoff,0,$end);

//echo $table;


// PHPQUERY

//phpQuery::newDocumentFileXHTML($filename);




// PHP-DOM-XPATH


function innerHTML($el) {
  $doc = new DOMDocument();
  $doc->appendChild($doc->importNode($el, TRUE));
  $html = trim($doc->saveHTML());
  $tag = $el->nodeName;
  return preg_replace('@^<' . $tag . '[^>]*>|</' . $tag . '>$@', '', $html);
}


$doc = new DomDocument();
$doc->load($filename);		// LOADS OF WARNINGS, FAILS
$xpath = new DOMXpath($doc);

$elements = $xpath->query('//table//td');

if (!is_null($elements)) {
  foreach ($elements as $element) {
    echo "<br/>[". $element->nodeName. "]";
    echo "<br/>". innerHTML($element);

    $nodes = $element->childNodes;
    foreach ($nodes as $node) {
      echo $node->nodeValue. "\n";
    }
  }
}