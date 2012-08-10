<?php
	// Set buffer to true
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
			<h1>Please wait...</h1>
		</div><!-- /header -->
		<div data-role="content" data-theme="d">
			<p>Sending message...</p>
		</div><!-- /content -->
	</div><!-- /page -->
</div>

<div data-role="panel" data-id="main">
	<div data-role="page" id="main">

		<div data-role="header">
			<h1>Lobby-O-Matic</h1>
		</div><!-- /header -->

		<div data-role="content" data-theme="b"><?php
	$emailtext = array();
	$toarray = array();
	$bill = query("select Title from Bills where BillID = ".($_GET["billid"] + 0));
	foreach ($_POST as $key => $p) {
		if ($key == $p) {
			$pol = query("select FullName, Email from Politicians where PoliticianID = ".($p + 0));
			if (count($pol) > 0) {
				$toarray[] = $pol[0][1];
				$emailtext[] = <<<EMAILTEXT
Dear {$pol[0][0]},

The following message has been sent from Lobby-O-Matic user {$_SESSION["username"]}, relating to the {$bill[0][0]} bill:

{$_POST["messagebody"]}

Thanks,

The Lobby-O-Matic team.
EMAILTEXT;
			}
		}
	}
	if (true) {
		echo("<pre>");
		print_r($emailtext);
		echo("\n\n");
		print_r($toarray);
		echo("</pre>");
	}
	?>
		</div><!-- /content -->

	<?php include('footer.php'); ?>

	</div><!-- /page -->

</div>

</body>
</html><?php
	foreach ($toarray as $key => $to) {
		/*mail(
			//$to
			"madman.bob@hotmail.co.uk"
			, '[Lobby-O-Matic] '.$_SESSION["username"]." on the ".$bill[0][0]." bill", $emailtext[$key], 'From: noreply@toastwaffle.com');*/
	}
?>