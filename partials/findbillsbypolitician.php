<html>
	<head>
		<title>Hi</title>
	</head>
	<body>
		<pre><?php
	include('config.php');

	function findBill($politicianid, $limit) {
		global $conn;
		$politician = query("select TopWords from Politicians where PoliticianID = $politicianid");
		return query("select *, match(BillText) against ('".$conn -> escape_string($politician[0][0])."') as `match` from Bills order by `match` desc limit $limit");
	}
	
	print_r(findBill(1, 10));

	$conn -> close();
	?></pre>Done
	</body>
</html>
