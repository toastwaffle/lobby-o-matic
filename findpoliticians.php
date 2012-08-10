<?php
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

	function getSearchtermPoliticians($searchterm, $limit) {
		global $conn;
		$longquery = sprintf("select Politicians.*, MATCH(Politicians.TopWords) AGAINST ('%s') as score from Politicians ORDER BY score DESC LIMIT 0, %u",$conn->real_escape_string($searchterm), $limit);
		return query($longquery);
	}
	
	//print_r(getRelatedPoliticians(1, 10));
	if (isset($_GET["billid"])) {
		$pols = query("select Politicians.PoliticianID, '', '', Politicians.Name from Politicians inner join PoliticianDepartments on Politicians.PoliticianID = PoliticianDepartments.PoliticianID where PoliticianDepartments.DepartmentID = ".($_GET["depid"] + 0)." order by Politicians.Name");
		if (count($pols) == 0) {
			$pols = getRelatedPoliticians($_GET["billid"] + 0, 10);
		}
	} else {
		$pols = query("select Politicians.PoliticianID, '', '', Politicians.Name from Politicians inner join PoliticianDepartments on Politicians.PoliticianID = PoliticianDepartments.PoliticianID where PoliticianDepartments.DepartmentID = ".($_GET["depid"] + 0)." order by Politicians.Name");
		if (count($pols) == 0) {
			$pols = getSearchtermPoliticians($_GET['searchterm'], 10);
		}
	}
	
	foreach ($pols as $p) {
		echo("
					<input type=\"checkbox\" name=\"".$p[0]."\" id=\"".$p[0]."\" />
					<label style=\"font-size:0.5pc;\" for=\"".$p[0]."\">".$p[3]."</label>");
	}
	
	?>
