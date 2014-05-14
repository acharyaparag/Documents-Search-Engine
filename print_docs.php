<?php

$xml=simplexml_load_file("trec.xml");
 
$doc_id = $_GET['doc_id'];
$docno = "I".$doc_id;
	
$parent = $xml->$docno;

 foreach($parent->children() as $child)
		    {
			    echo $child. "<br><br>";
			}			

?>