<html>
	<head>
		<title>Hi</title>
	</head>
	<body>
		<pre><?php
	include('config.php');
	setlocale(LC_ALL, 'en_UK.utf8');
	
	$xml = file_get_contents("http://data.parliament.uk/resources/members/api/commons");
	$xml = str_replace("m:id","id", $xml);
	$xml = str_replace("m:website","website", $xml);
	$xml = preg_replace("/<\/[^> ]+:/i", "</", $xml);
	$xml = preg_replace("/<[^> ]+:/i", "<", $xml);
	$a = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
	//print_r($a);

	foreach ($a["commonsMember"] as $pol) {
		$xml = file_get_contents("http://data.parliament.uk/resources/members/api/biography/".$pol["@attributes"]["id"]."/");
		$xml = preg_replace("/<\/[^> ]+:/i", "</", $xml);
		$xml = preg_replace("/<[^> ]+:/i", "<", $xml);
		$b = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
		$politician = query("select PoliticianID from Politicians where Name like '".$conn -> escape_string(iconv('utf8', 'ascii//TRANSLIT', $pol["firstName"]))."%".$conn -> escape_string(iconv('utf8', 'ascii//TRANSLIT', $pol["lastName"]))."' and Gender = ''");
		if (count($politician) > 0) {
			$address = "";
			$phone = "";
			$fax = "";
			$email = "";
			foreach ($b["addresses"]["address"] as $addressgroup) {
				if ($addressgroup["addressLine1"]."" != "Array" && $addressgroup["addressLine1"] != "No constituency office publicised" && $addressgroup["addressLine1"] != "House of Commons") {
					$address = $addressgroup["addressLine1"];
					if ($addressgroup["addressLine2"]."" != "Array") {
						$address .= "\n".$addressgroup["addressLine2"];
					}
					if ($addressgroup["addressLine3"]."" != "Array") {
						$address .= "\n".$addressgroup["addressLine3"];
					}
					if ($addressgroup["addressLine4"]."" != "Array") {
						$address .= "\n".$addressgroup["addressLine4"];
					}
					if ($addressgroup["addressLine5"]."" != "Array") {
						$address .= "\n".$addressgroup["addressLine5"];
					}
					if ($addressgroup["postCode"]."" != "Array") {
						$address .= "\n".$addressgroup["postCode"];
					}
				}
				if ($addressgroup["phone"]."" != "Array" && $addressgroup["phone"] != "020 7219 4426") {
					$phone = $addressgroup["phone"];
				}
				if ($addressgroup["fax"]."" != "Array" && $addressgroup["fax"] != "020 7219 4964") {
					$fax = $addressgroup["fax"];
				}
				if ($addressgroup["email"]."" != "Array" && $addressgroup["email"] != "chalkiasg@parliament.uk") {
					$email = $addressgroup["email"];
				}
			}
			
			query("update Politicians set FullName = '".$conn -> escape_string($b["fullName"])."', Gender = '".$conn -> escape_string($b["gender"])."', PhotoUrl = '".$conn -> escape_string($b["lowResPhoto"])."', WebsiteUrl = '".$conn -> escape_string($b["website"])."', Address = '".$conn -> escape_string($address)."', Phone = '".$conn -> escape_string($phone)."', Fax = '".$conn -> escape_string($fax)."', Email = '".$conn -> escape_string($email)."' where PoliticianID = ".$politician[0][0]);
		}
		//print_r($b);
	}
	
	$conn -> close();
?></pre>
	</body>
</html>
