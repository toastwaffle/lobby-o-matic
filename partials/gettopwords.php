<html>
	<head>
		<title>Hi</title>
	</head>
	<body>
		<pre><?php
	include('config.php');

	$politicians = query("select PoliticianID, WikiUrl, GuardianUrl, BBCUrl from Politicians where TopWords = ''");
	foreach ($politicians as $row) {
		$xml = "";
		foreach($row as $x) {
			if (preg_match("/^http:\/\//i", $x) > 0) {
				$filecontents = file_get_contents($x);
				$filecontents = substr($filecontents, strpos($filecontents, "<body"));
				$filecontents = substr($filecontents, 0, strpos($filecontents, "</body"));
				$xml .= " ".$filecontents;
			}
		}
		$xml = preg_replace("/<[^>]+>/i", "", $xml);
		$xml = preg_replace("/\\s+/i", " ", $xml);
		//print_r($xml);
		$words = extractCommonWords($xml, 20);
		$polwords = "";
		foreach ($words as $word => $count) {
			$polwords .= $word." ";
			//echo($word." ");
		}
		query("update Politicians set TopWords = '".$conn -> escape_string($polwords)."' where PoliticianID = ".$row[0]);
	}
	
	//print_r($politicians);
	$conn -> close();
	?></pre>Done
	</body>
</html>
