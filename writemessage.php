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
	
	$bill = query("select BillID, Link, Title, Description, BillText, BillPDFLoc from Bills where BillID = ".($_GET["billid"] + 0));
	if (count($bill) == 0) {
		$bill = query("select BillID, Link, Title, Description, BillText, BillPDFLoc from Bills where BillID = 1");
	}
?>
<!DOCTYPE html> 
<html> 
	<head> 
	<title>Lobby-O-Matic</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="./resources/style.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.splitview.css" />
	<link rel="stylesheet"  href="./resources/jquery.mobile.grids.collapsible.css" />
	<script type="text/javascript" src="./resources/jquery-1.7.1.js"></script>
	<script type="text/javascript" src="./resources/jquery.mobile.splitview.js"></script>
	<script type="text/javascript" src="./resources/jquery.mobile.js"></script>
	<script type="text/javascript" src="./resources/iscroll-wrapper.js"></script>
	<script type="text/javascript" src="./resources/iscroll.js"></script>
	<style type="text/css">
iframe {
	position:absolute;
	bottom:0px;
	left:0px;
	right:0px;
	width:100%;
	border:0px;
}
	</style>
</head> 
<body> 

<div data-role="panel" data-id="menu">
	<div data-role="page" id="menu">
		<div data-role="header" data-theme="e">
			<h1>Letter</h1>
		</div><!-- /header -->
		<div data-role="content" data-theme="d">
			<form method="post" action="">
				To:
				<select onchange="$('#chosepolitician').load('./findpoliticians.php?depid=' + $(this).val() + '&billid=<?php echo($bill[0][0]); ?>')">
					<option value="0">Recommended</option><?php
	$departments = query("select DepartmentID, DepartmentName from Departments order by DepartmentName");
	foreach ($departments as $d) {
		echo("
					<option value=\"".$d[0]."\">".$d[1]."</option>");
	}
	?>
				</select>
				<select id="chosepolitician"><?php
	$relpols = getRelatedPoliticians($bill[0][0], 10);
	foreach ($relpols as $rp) {
		echo("
					<option value=\"".$rp[0]."\">".$rp[3]."</option>");
	}
	?>
				</select>
				<textarea></textarea>
				<input type="submit" value="Send" />
			</form>
		</div><!-- /content -->
	</div><!-- /page -->
</div>

<div data-role="panel" data-id="main">
	<div data-role="page" id="main">

		<div data-role="header">
			<h1>Lobby-O-Matic</h1>
		</div><!-- /header -->

		<div data-role="content" data-theme="b">
			<p><?php echo($bill[0][2]); ?></p>
			<p><?php echo($bill[0][3]); ?></p>
			<p><iframe src="http://docs.google.com/viewer?url=<?php echo(urlencode($bill[0][5])); ?>&embedded=true" /></p>
		</div><!-- /content -->

	<?php include('footer.php'); ?>

	</div><!-- /page -->

</div>

</body>
</html>
