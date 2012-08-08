<html>
	<head>
		<title>Hi</title>
	</head>
	<body>
		<pre><?php
	include('config.php');

	$xml = file_get_contents("http://www.theyworkforyou.com/api/getMPs?key=A2VvvjBaVYukBjgsw5GbNyDt&output=xml");
	$a = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
	//print_r($a);
	
	foreach ($a["match"] as $key => $x) {
		$pol = query("select PoliticianID from Politicians where theyworkforyou_member_id = ".$x["member_id"]);
		if (count($pol) == 0) {
			$partyid = 0;
			$party = query("select PartyID from Parties where PartyName = '".$conn -> escape_string($x["party"])."'");
			if (count($party) > 0) {
				$partyid = $party[0][0];
			} else {
				query("insert into Parties (PartyName) values ('".$conn -> escape_string($x["party"])."')");
				$partyid = $conn -> insert_id;
			}
			
			$conid = 0;
			$constituency = query("select ConstituencyID from Constituencies where ConstituencyName = '".$conn -> escape_string($x["constituency"])."'");
			if (count($constituency) > 0) {
				$conid = $constituency[0][0];
			} else {
				query("insert into Constituencies (ConstituencyName) values ('".$conn -> escape_string($x["constituency"])."')");
				$conid = $conn -> insert_id;
			}
		
			$xml = file_get_contents("http://www.theyworkforyou.com/api/getMPInfo?key=A2VvvjBaVYukBjgsw5GbNyDt&output=xml&id=".$x["person_id"]."&fields=wikipedia_url%2Cguardian_mp_summary%2Cwrans_departments%2Cbbc_profile_url");
			$mpinfo = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
			
			query("insert into Politicians (theyworkforyou_member_id, theyworkforyou_person_id, Name, PartyID, ConstituencyID, WikiUrl, GuardianUrl, BBCUrl) values (".$x["member_id"].", ".$x["person_id"].", '".$conn -> escape_string($x["name"])."', $partyid, $conid, '".$conn -> escape_string($mpinfo["wikipedia_url"])."', '".$conn -> escape_string($mpinfo["guardian_mp_summary"])."', '".$conn -> escape_string($mpinfo["bbc_profile_url"])."')");
			$politicianid = $conn -> insert_id;
			
			$departments = explode(", ", $mpinfo["wrans_departments"]);
			
			foreach ($departments as $d) {
				$departmentid = 0;
				$dep = query("select DepartmentID from Departments where DepartmentName = '".$conn -> escape_string($d)."'");
				if (count($dep) > 0) {
					$departmentid = $dep[0][0];
				} else {
					query("insert into Departments (DepartmentName) values ('".$conn -> escape_string($d)."')");
					$departmentid = $conn -> insert_id;
				}
				query("insert into PoliticianDepartments (PoliticianID, DepartmentID) values ($politicianid, $departmentid)");
			}
		}
	}
	
	/*
	member_id
	person_id
	name
	party
	constituency
	
	writetothem_responsiveness_mean_yyyy
	wikipedia_url
	guardian_mp_summary
	wrans_departments
	bbc_profile_url
	*/

	$conn -> close();
	?></pre>Done
	</body>
</html>
