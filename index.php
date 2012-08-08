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

<div data-role="panel" data-id="main">
<div data-role="page" id="main">

	<div data-role="header">
		<h1>Lobby-O-Matic</h1>
	</div><!-- /header -->

	<div data-role="content" data-theme="b">
		<?php echo($messages); ?>	
		<p>Welcome to Lobby-O-Matic. This is the place to get in contact with MPs regarding either upcoming Bills in Parliament or the topic of your choice.</p>
		<p>To get started, search for a topic or select a bill below</p>
		<form action="writemessage.php" method="post">
			<input type="text" name="searchterm" />
			<input type="submit" data-icon="search" value="Search" />
		</form>
		<br />
		<ul data-role="listview" data-inset="true" data-filter="true">
			<?php
				$result = $conn->query('SELECT Title,Description,BillID FROM Bills ORDER BY Title ASC');
				if (!$result) {
					die($conn->error);
				}
				$billPopups = array();
				while ($row = $result->fetch_assoc()) {
					if (strlen($row['Description']) > 0) {
						echo('<li><a href="#billPopup'.$row['BillID'].'" data-rel="dialog" data-transition="pop" title="'.$row['Description'].'">'.$row['Title'].'</a></li>'.PHP_EOL);
						$billPopups[] = '<div data-role="page" id="billPopup'.$row['BillID'].'">
											<div data-role="header" data-theme="e">
												<h1>'.$row['Title'].'</h1>
											</div><!-- /header -->
											<div data-role="content" data-theme="d">
												<p>'.$row['Description'].'</p>
												<p><a data-role="button" data-direction="forward" href="writemessage.php?billid='.$row['BillID'].'">Write to MPs about this Bill</a></p>
											</div><!-- /content -->
										</div><!-- /page -->';
					} else {
						echo('<li><a href="#billPopup'.$row['BillID'].'" data-rel="dialog" data-transition="pop">'.$row['Title'].'</a></li>'.PHP_EOL);
						$billPopups[] = '<div data-role="page" id="billPopup'.$row['BillID'].'">
											<div data-role="header" data-theme="e">
												<h1>'.$row['Title'].'</h1>
											</div><!-- /header -->
											<div data-role="content" data-theme="d">
												<p>There is no description for this bill.</p>
												<p><a data-role="button" data-direction="forward" href="writemessage.php?billid='.$row['BillID'].'">Write to MPs about this Bill</a></p>
											</div><!-- /content -->
										</div><!-- /page -->';
					}
				}
			?>
		</ul>
	</div><!-- /content -->

<?php include('footer.php'); ?>

</div><!-- /page -->

<?php echo(implode(PHP_EOL,$billPopups)); ?>
</div>

</body>
</html>