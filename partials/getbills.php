<?php

	include('config.php');
	
	$xml = file_get_contents("http://services.parliament.uk/bills/AllBills.rss");
	$xml = str_replace("a10:", "", $xml);
	$a = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
	//print_r($a);
	
	foreach ($a["channel"]["item"] as $x) {
		if (count(query("select BillID from Bills where GUID = '".$conn -> escape_string($x["guid"])."'")) == 0) {
			$billtext = getBillText($x["link"]);
			if ($billtext != "") {
				query("insert into Bills (GUID, Link, Title, Description, BillText) values ('".$conn -> escape_string($x["guid"])."', '".$conn -> escape_string($x["link"])."', '".$conn -> escape_string($x["title"])."', '".$conn -> escape_string($x["description"])."', '".$conn -> escape_string($billtext)."')");
				$billid = $conn -> insert_id;
		
				foreach ($x["category"] as $y) {
					$id = query("select CategoryID from Category where CategoryName = '".$conn -> escape_string($y)."'");
					if (count($id) == 0) {
						query("insert into Category (CategoryName) values ('".$conn -> escape_string($y)."')");
					$id = query("select CategoryID from Category where CategoryName = '".$conn -> escape_string($y)."'");
					}
					query("insert into BillCategory (BillID, CategoryID) values (".$billid.", ".$id[0][0].")");
				}
			}
		}
	}
	
	$conn -> close();
?>
