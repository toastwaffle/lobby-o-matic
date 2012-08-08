<?php
	header('Content-Type: text/plain');
	//header('Content-Type: application/json');

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
	
	$guardianarticles = array();

	foreach ($_GET as $billid => $checked) {
		$query = sprintf('SELECT BillText FROM Bills WHERE BillID = %s',$conn->real_escape_string($billid));

		$sqlresult = $conn->query($query);
		if ($sqlresult) {
			while($row = $sqlresult->fetch_assoc()) {
				$words = extractCommonWords($row['BillText'],2);
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
			}
		}
	}

	$guardianarticles = array_filter(array_map($array_map, $guardianarticles),$array_filter);

	print_r($guardianarticles);
	//echo(json_encode($guardianarticles));
?>