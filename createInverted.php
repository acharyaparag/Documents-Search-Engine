<!-- http://people.cs.clemson.edu/~pachary/index.php-->

<html>
<head>
</head>
<body>
<?php

include_once("stemming.php");
$outFile="./init.sql";
$handle = fopen($outFile, 'w');	
$outString = "";
$outString .= "drop table if exists invertedIndexTable;\r\n"  ;
$outString .= "CREATE TABLE invertedIndexTable (word CHAR(255),frequency INT,docID INT,PRIMARY KEY(word,docID));\r\n";

$inputfile = file_get_contents('./trec.txt');
$stopfile = file_get_contents('./stopwords.txt');
$stopwords = explode("\r\n",$stopfile);

$lines = file('trec.txt');
$ICounter = 0;

$xml = "<?xml version='1.0' encoding='UTF-8'?>\n<note>\n";
foreach ($lines as $line) {
	 if (preg_match("/I>/", $line))
		{	
			$string = $line;
			$replacement = "I".$ICounter.">";
			$pattern = "/I>/";
  
			if (preg_match("/<\/I>/", $line))
				{
					$ICounter++;
				}

			$line = preg_replace($pattern, $replacement, $string);
		}	
		
	if (preg_match("/U>/", $line))
		{	
			$string = $line;
			$replacement = "U".$ICounter.">";
			$pattern = "/U>/";
     		$line = preg_replace($pattern, $replacement, $string);
		}

	if (preg_match("/S>/", $line))
		{	
			$string = $line;
			$replacement = "S".$ICounter.">";
			$pattern = "/S>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			

	if (preg_match("/M>/", $line))
		{	
			$string = $line;
			$replacement = "M".$ICounter.">";
			$pattern = "/M>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			

	if (preg_match("/T>/", $line))
		{	
			$string = $line;
			$replacement = "T".$ICounter.">";
			$pattern = "/T>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			



	if (preg_match("/P>/", $line))
		{	
			$string = $line;
			$replacement = "P".$ICounter.">";
			$pattern = "/P>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			

	if (preg_match("/W>/", $line))
		{	
			$string = $line;
			$replacement = "W".$ICounter.">";
			$pattern = "/W>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			


	if (preg_match("/A>/", $line))
		{	
			$string = $line;
			$replacement = "A".$ICounter.">";
			$pattern = "/A>/";
			$line = preg_replace($pattern, $replacement, $string);
		}			
		
	$line=preg_replace('/&/', '&amp;', $line);
    $xml .= $line;
}
$xml .= "\n</note>";

file_put_contents('trec.xml', $xml);
$xml=simplexml_load_file("trec.xml");
$ICounter = 0;

     foreach($xml->children() as $child)
	    {
		 $countStem = array();
		 $stemArray = array();
		 $index = 0;
				
		 foreach($child->children() as $grandchild)
		    {
				$string = explode(" ",strtolower($grandchild));
				$stringAlpha = preg_replace("/[^a-zA-Z0-9]+/", " ", $string);
				$stringAlpha = implode(" ",$stringAlpha);
				$stringAlpha = explode(" ",$stringAlpha);
				$result = array_diff($stringAlpha, $stopwords);
				$result = array_values($result);
				for ( $i = 0;$i < count($result) ;$i++)
					{
					  if ($result[$i]!=="")
					  {
						$stem = PorterStemmer::Stem($result[$i]);
						//echo "word,stem :".$result[$i]." : ".$stem."<br>";
						if (in_array($stem, $stemArray))
							{
								$key = array_search($stem, $stemArray); 
								$countStem[$key] = $countStem[$key]+1;
							}
						else
							{
								$countStem[$index] = 1;
								$stemArray[$index] = $stem;
								$index++;
							} 	
					  }	
					}
			}

			for ($i=0;$i<count($stemArray);$i++)
			{
					$outString .= "INSERT INTO invertedIndexTable VALUES('$stemArray[$i]',$countStem[$i],$ICounter);\r\n";
			} 
			//echo "test1:".count($stemArray)."<br>";
			//echo "test2:".count($countStem)."<br>";				
			unset($countStem);
			unset($stemArray);
			$ICounter++;
        }			
		  
echo "Inverted Index Created Successfully";			  
fwrite($handle, $outString);
fclose($handle);

?>
</body>
</html>