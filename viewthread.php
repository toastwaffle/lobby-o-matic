<?php
	include('config.php');
	if (!isset($_SESSION['username'])) {
		header('Location: login.php?redirect=index.php&pleaselogin');
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
</head> 
<body> 

<div data-role="panel" data-id="menu">
	<div data-role="page" id="menu">
		<div data-role="header" data-theme="e">
			<h1></h1>
		</div><!-- /header -->
		<div data-role="content" data-theme="d">
			<p><?php
	$thread = query("select threadkey, initialuser, ifnull(BillID, '') from Threads where threadkey = '".$conn->real_escape_string($_GET['threadkey'])."'");
	if (count($thread) > 0) {
		if ($thread[0][2] != "") {
			$bill = query("select Title, Description from Bills where BillID = ".$thread[0][2]);
			if (count($bill) > 0) {
				echo($bill[0][0]."<br/>
				<br/>
				".$bill[0][1]);
			}
		}
	} else {
		?>Thread not found.<?php
	}
	?>

			</p>
		</div><!-- /content -->
	</div><!-- /page -->
	<?php echo(implode(PHP_EOL,$billPopups)); ?>
</div>

<div data-role="panel" data-id="main">
	<div data-role="page" id="main">

		<div data-role="header">
			<a href="./" data-ajax="false" class="ui-btn-active">Back</a>
			<h1>Lobby-O-Matic</h1>
			<a href="./logout.php" data-ajax="false" style="float:right;">Logout</a>
		</div><!-- /header -->

		<div data-role="content" data-theme="b">
			<table style="width:100%;"><?php
	$messages = query("select Emails.fromname, Emails.message, ifnull(Politicians.PoliticianID, '') from Emails left join Politicians on Emails.fromemail = Politicians.Email where Emails.threadkey = '".$thread[0][0]."'");
	foreach ($messages as $message) {
		if ($message[2] == "") {
			$d = "d";
		} else {
			$d = "e";
		}
		echo("
				<tr>
					<td style=\"width:20%;\" class=\"ui-bar-{$d} ui-corner-left\">".$message[0]."</td>
					<td class=\"ui-bar-{$d} ui-corner-right\">".nl2br($message[1])."</td>
				</tr>");
	}
	?>
			</table>
		</div><!-- /content -->

	<?php include('footer.php'); ?>

	</div><!-- /page -->

</div>

</body>
</html>
