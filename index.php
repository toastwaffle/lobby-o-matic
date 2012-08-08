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
	<link rel="stylesheet" href="./resources/jquery.mobile-1.1.1.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.structure-1.1.1.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.theme-1.1.1.css" />
	<link rel="stylesheet" href="./resources/style.css" />
	<script src="./resources/jquery-1.7.1.min.js"></script>
	<script src="./resources/jquery.mobile-1.1.1.min.js"></script>
</head> 
<body> 

<div data-role="page" id="main">

	<div data-role="header">
		<h1>Lobby-O-Matic</h1>
	</div><!-- /header -->

	<div data-role="content" data-theme="b">
		<?php echo($messages); ?>	
		<p>Welcome to Lobby-O-Matic. This is the place to get in contact with MPs regarding either upcoming Bills in Parliament or the topic of your choice.</p>
		<p>To get started, search for a topic or select a bill below</p>
		<form action="search.php" method="post">
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
					echo('<li><a href="#billPopup'.$row['BillID'].'" data-rel="dialog" data-transition="pop" title="'.$row['Description'].'">'.$row['Title'].'</a></li>'.PHP_EOL);
					$billPopups[] = '<div data-role="page" id="billPopup'.$row['BillID'].'">
										<div data-role="header" data-theme="e">
											<h1>'.$row['Title'].'</h1>
										</div><!-- /header -->
										<div data-role="content" data-theme="d">
											<p>'.$row['Description'].'</p>
											<p><a data-role="button" data-direction="forward" href="bills.php?billid='.$row['BillID'].'">Write to MPs about this Bill</a></p>
										</div><!-- /content -->
									</div><!-- /page -->';
				}
			?>
		</ul>
	</div><!-- /content -->

<?php include('footer.php'); ?>

</div><!-- /page -->

<?php echo(implode(PHP_EOL,$billPopups)); ?>

</body>
</html>