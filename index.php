<!-- http://people.cs.clemson.edu/~pachary/assign8/index.php-->
<html> <body>
<link href="login.css" rel="stylesheet" type="text/css" />
<?php

 include_once("dbconnect.inc.php");
 include_once("stemming.php");


 $xml=simplexml_load_file("trec.xml");
 $stopfile = file_get_contents('./stopwords.txt');
 $stopwords = explode("\r\n",$stopfile);
 $columns = array("Title","Author","Source","Ranking Score");
 $orStop = array("or");
 $input_query = "";	
 $orString = "";
 $andString = "";
 $andArray = array();
 $orArray = array();
 $finalArray = array();
 $stemArrayAnd = array();
 $stemArrayOr =  array();
 $query_and = "";
 $query_or = "";
 $origAnd = "";
 $origOr = "";
 $orResult = array();
 $andResult = array();
 $scoreArray = array();
 $prod = 1;
 $sum = 1;
 $scoreAnd = "";
 $scoreOr = "";
 

  $mysqli = new mysqli($host, $user, $password, $database);
 // Check connection
 if (mysqli_connect_errno())
    {
     echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }	
	
$result = "";	
$input_string = "";		
$temp = $_SERVER['PHP_SELF'];

echo "<form name='select' id='select' action='$temp' method='post' >";
echo "SEARCH QUERY";
echo str_repeat('&nbsp;', 5); 
echo "<input name='query' type='text' id='query' size='100' value='$input_string'>";
echo str_repeat('&nbsp;', 5);
echo " <input type='submit' name='submit' value='Search'>";
echo "</form>";

		
    if ($_SERVER["REQUEST_METHOD"] == "POST")
		{
	/*	if (empty($_POST["query"]))
			{
				$inputstring = " ";	   
			}
		else
			{	*/		
			     $input_string = $_POST["query"];	 
				 if (preg_match('/\((.*?)\)/', $input_string, $out))
					{
					  $orString = $out[1];
					}
				// echo "<br>";
				 $andString = preg_replace('/\((.*?)\)/', "", $input_string);
				 $origAnd = $andString;
				// echo "input and :";echo $andString;
				// echo "<br>";
				 if (empty($origAnd) !== true or $origAnd == '0')
					{
						$andString = preg_replace("/[^a-zA-Z0-9]+/", " ", $andString);
						$andString = preg_split("/[\s]+/", strtolower(trim($andString)));
						//echo "stem:";print_r($andString);
						//echo "<br>";
						$andResult = array_diff($andString, $stopwords);
						$andResult = array_values($andResult);
					}	
			
			//	echo "input or string:".$orString."<br>";	
				$origOr = $orString;	
                if(empty($origOr) !== true or $origOr == '0') 			
		          {
				  	$orString = preg_replace("/[^a-zA-Z0-9]+/", " ", $orString);
					$orString = preg_split("/[\s]+/", strtolower(trim($orString)));
					$orResult = array_diff($orString, $stopwords,$orStop);
					$orResult = array_values($orResult);
					//echo "input or :";print_r($orResult);
				  } 	
		
				//echo "<br>";

				
						  
				for ( $i = 0;$i < count($andResult) ;$i++)
					{
					  $stemArrayAnd[$i] = PorterStemmer::Stem($andResult[$i]);
					}
	
				for ( $i = 0;$i < count($stemArrayAnd) ;$i++)
					{
					 if ($i == 0)
					 	$query_and = "select frequency,docID,count(docID) from invertedIndexTable where word in ('$stemArrayAnd[$i]'";
					 else	
						$query_and .= " , '$stemArrayAnd[$i]'";
					 if($i == count($stemArrayAnd)-1)	
						{				
						 $countWordsAnd = count($stemArrayAnd);
 						 $query_and .= ") group by docID having count(docID) >= '$countWordsAnd'";	
						} 
					}  
	
				//echo "and query:".$query_and."<br>";
				if ( count($andResult) > 0) 
				   {
				    $resultqueryAnd = $mysqli->query($query_and) or die($mysqli->error.__LINE__);						  
					while($row = $resultqueryAnd->fetch_row()) 
						{
						array_push($andArray,$row[1]);
						}	
					}	
					
					
					
				for ( $i = 0;$i < count($orResult) ;$i++)
					{
					  $stemArrayOr[$i] = PorterStemmer::Stem($orResult[$i]);
					}

				for ( $i = 0;$i < count($stemArrayOr) ;$i++)
					{
					    if ($i == 0 )				
							$query_or = "select word,frequency,docID from invertedIndexTable where word in ('$stemArrayOr[$i]'";
						else
							$query_or .= " , '$stemArrayOr[$i]'";
						 if($i == count($stemArrayOr)-1)		
							$query_or .= ")";	
					}  

				//echo "query or:".$query_or."<br>";
				if ( count($orResult) > 0) 
				   {	
					$resultqueryOr = $mysqli->query($query_or) or die($mysqli->error.__LINE__);						  
					while($row = $resultqueryOr->fetch_row()) 
						{
						array_push($orArray,$row[2]);
						}
					}	
					
								//echo "and array:"; print_r($andArray);  echo"<br>";			 echo "or array:";print_r($orArray);echo"<br>";	  
					  
				if ( $origAnd!=="" and $origOr!=="")	  
					{
					 $finalArray = array_intersect($andArray,$orArray);	
					// echo "INSIDE Final"."<br>";
					 //print_r($finalArray);
					 $finalArray = array_values($finalArray);
					// print_r($finalArray);
					 //echo "<br>";		
					} 
				else if ( $origAnd!=="" )
				     $finalArray = $andArray;	
				else if ( $origOr!=="" )
				     $finalArray = $orArray;

				
					for ( $j = 0;$j < count($stemArrayAnd) ;$j++)
								{
								if ($j == 0)
									{
									 $scoreAnd = "select frequency from invertedIndexTable where word in ('$stemArrayAnd[$j]'";
									}
								else	
									$scoreAnd .= " , '$stemArrayAnd[$j]'";
								}	
								
					for ( $k = 0;$k < count($stemArrayOr) ;$k++)
								{
								if ($k == 0)
									{
									 $scoreOr = "select frequency from invertedIndexTable where word in ('$stemArrayOr[$k]'";
									}
								else	
									$scoreOr .= " , '$stemArrayOr[$k]'";
								}
									
			    if(count($finalArray) > 0 and $input_string!=="") 
                  {
					echo "<table border='1'>
					<tr bgcolor= 'blue' >";
					for ( $i = 0; $i < count($columns) ; $i++)
						{
							echo "<th><font color='white'>" . $columns[$i] . "</font></th>";
						}
					echo "</tr>";
				
						for ($i = 0;$i<count($finalArray);$i++)
						   {
		                    $one = "I".$finalArray[$i];
							$two = "T".$finalArray[$i];
							$Title = $xml->$one->$two ;
							$two = "A".$finalArray[$i]; 
							$Author = $xml->$one->$two ;
							$two = "S".$finalArray[$i];
							$Source = $xml->$one->$two ;
							$id = $finalArray[$i];
							
							$prod = 1;
							if ( count($stemArrayAnd)> 0)
							   {
					
									$scoreAndQuery = $scoreAnd.") and docID = '$id'";
									$resultscoreAnd = $mysqli->query($scoreAndQuery) or die($mysqli->error.__LINE__);						  
									while($row = $resultscoreAnd->fetch_row()) 
										{
											$prod = $prod *$row[0];
										}
								}		
							
							if ( count($stemArrayOr) > 0)
							   {
							   	    $sum = 0;
									$scoreOrQuery = $scoreOr.") and docID = '$id'";
									$resultscoreOr = $mysqli->query($scoreOrQuery) or die($mysqli->error.__LINE__);						  
									while($row = $resultscoreOr->fetch_row()) 
										{
											$sum = $sum + $row[0];
										}
							   }			
					
							$Score = $prod*$sum;
				  
							echo "<tr>";	
							echo '<td><a href="print_docs.php?doc_id='.urlencode($finalArray[$i]).'">'.$Title.'</a></td>';							
							echo "<td>" . $Author . "</td>";		
							echo "<td>" . $Source . "</td>";		
							echo "<td>" . $Score . "</td>";			
	            			echo "</tr>";
							}
							
					echo "</table>";
					echo "<br>";    	
					echo "No of Documents : ".count($finalArray);	
					
		         }  
			   else
					echo "No Documents Found";	 
		}					
	
   mysqli_close($mysqli); 
?>


</body>
</html>  




