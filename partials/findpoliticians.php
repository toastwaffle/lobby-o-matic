<html>
	<head>
		<title>Hi</title>
	</head>
	<body>
		<pre><?php
	include('config.php');

	function getRelatedPoliticians($billid, $limit) {
		global $conn;
		$pols = query("select PoliticianID, TopWords from Politicians");
		$longquery = "select Politicians.*, scoretable.score from (";
		$first = true;
		foreach ($pols as $pol) {
			if ($first) {
				$first = false;
			} else {
				$longquery .= " union ";
			}
			$longquery .= "select ".$conn -> escape_string($pol[0])." as PID, match(BillText) against ('".$conn -> escape_string($pol[1])."') as `score` from Bills where BillID = $billid";
		}
		$longquery .= " order by score desc limit $limit) as `scoretable` inner join Politicians on Politicians.PoliticianID = scoretable.PID";
		return query($longquery);
	}
	
	print_r(getRelatedPoliticians(1, 10));

	$conn -> close();
	?></pre>Done
	</body>
</html>
