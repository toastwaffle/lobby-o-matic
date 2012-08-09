<?php

	include('config.php');

	$array_map = function($item) {
		if ($item->fields->body == '<!-- Redistribution rights for this field are unavailable -->') {
			return null;
		}
		$item->fields->body = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item->fields->body);
		$position = strpos($item->fields->body, '<div class="gu_advert">');
		if (($position !== False) && ($position > 0)) {
			$item->fields->body = substr($item->fields->body, 0, $position);
		}
		return $item;
	};

	$array_filter = function($item) {
		return ($item === null) ? false : true;
	};

	$uasort_function = function($a,$b) {
		if ($a['relevance'] > $b['relevance']) {
			return -1;
		} else if ($a['relevance'] < $b['relevance']) {
			return 1;
		} else {
			return 0;
		}
	};
	
	$xml = file_get_contents("http://services.parliament.uk/bills/AllBills.rss");
	$xml = str_replace("a10:", "", $xml);
	$a = json_decode(json_encode((array) simplexml_load_string($xml)), 1);
	//print_r($a);
	
	$pdfurl = "";
	foreach ($a["channel"]["item"] as $x) {
		if (count(query("select BillID from Bills where GUID = '".$conn -> escape_string($x["guid"])."'")) == 0) {
			$billtext = getBillText($x["link"], $pdfurl);
			if ($billtext != "") {
				query("insert into Bills (GUID, Link, Title, Description, BillText, BillPDFLoc) values ('".$conn -> escape_string($x["guid"])."', '".$conn -> escape_string($x["link"])."', '".$conn -> escape_string($x["title"])."', '".$conn -> escape_string($x["description"])."', '".$conn -> escape_string($billtext)."', '".$conn -> escape_string($pdfurl)."')");
				$billid = $conn -> insert_id;
		
				foreach ($x["category"] as $y) {
					$id = query("select CategoryID from Category where CategoryName = '".$conn -> escape_string($y)."'");
					if (count($id) == 0) {
						query("insert into Category (CategoryName) values ('".$conn -> escape_string($y)."')");
					$id = query("select CategoryID from Category where CategoryName = '".$conn -> escape_string($y)."'");
					}
					query("insert into BillCategory (BillID, CategoryID) values (".$billid.", ".$id[0][0].")");
				}
	
				$guardianarticles = array();

				$words = extractCommonWords($billtext,2);
				$guardianurl = sprintf('http://content.guardianapis.com/search?q=%s'.
					'&format=json&show-fields=headline%%2Cbody%%2Cstandfirst&show-references=all&api-key=%s',
					implode('+', array_keys($words)),
					$guardianapikey);
				echo $guardianurl;
				$guardianresult = file_get_contents($guardianurl);
				$result = json_decode($guardianresult);
				if ($result->response->total > 0) {
					$guardianarticles = array_merge($guardianarticles,$result->response->results);
				}

				$guardianarticles = array_filter(array_map($array_map, $guardianarticles),$array_filter);

				foreach ($guardianarticles as $article) {
					query(sprintf("INSERT INTO Articles (billid,title,body,description) VALUES ('%s','%s','%s','%s')",
						$conn->real_escape_string($billid),
						$conn->real_escape_string($article['fields']['headline']),
						$conn->real_escape_string($article['fields']['body']),
						$conn->real_escape_string($article['fields']['standfirst'])));
					$articleid = $conn -> insert_id;

					$baseurl = 'http://api.opencalais.com/tag/rs/enrich';

					$ch = curl_init($baseurl);
					curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, substr($billtext,0,100000));
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'x-calais-licenseID: nnjdz39esaj7uab2x4s7qwvu',
						'Content-Type: text/raw',
						'Content-Length: ' . strlen($billtext),
						'Accept: application/json'));
					$result = curl_exec($ch);
					curl_close($ch);

					$data = json_decode($result,true);

					$document = $data['doc']['info']['document'];

					unset($data['doc']);

					$information = array();

					foreach ($data as $key => $value) {
						switch ($value['_typeGroup']) {
							case 'entities':
								$entities[$value['_type']][$value['name']] = array(
									'instances'=>$value['instances'],
									'relevance'=>$value['relevance']);
								break;
							default:
								break;
						}
					}

					foreach (array_keys($entities) as $key) {
						uasort($entities[$key],$uasort_function);
					}

					foreach ($entities as $entitytype => $entitiesoftype) {
						foreach($entitiesoftype as $entityname => $entity) {
							query(sprintf("INSERT INTO Entities (articleid, type, name, relevance) VALUES ('%s','%s','%s',%s)",
								$conn->real_escape_string($articleid),
								$conn->real_escape_string($entitytype),
								$conn->real_escape_string($entityname),
								$conn->real_escape_string($entity['relevance'])));
							$entityid = $conn -> insert_id;

							foreach($entity['instances'] as $instance) {
								query(sprintf("INSERT INTO EntityInstances (entityid, prefix, exact, suffix, offset, length) VALUES ('%s','%s','%s','%s','%s','%s')",
									$conn->real_escape_string($entity),
									$conn->real_escape_string($instance['prefix']),
									$conn->real_escape_string($instance['exact']),
									$conn->real_escape_string($instance['suffix']),
									$conn->real_escape_string($instance['offset']),
									$conn->real_escape_string($instance['length'])));
							}
						}
					}
				}
			}
		}
	}
	
	$conn -> close();
?>
