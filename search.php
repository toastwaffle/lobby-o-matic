<?php
	include('config.php');
	if (!isset($_SESSION['username'])) {
		if (isset($_POST['searchterm'])) {
			$_SESSION['searchterm'] = $_POST['searchterm'];
		}
		header('Location: login.php?redirect=search.php&pleaselogin');
	}

	function getRelatedPoliticians($searchterm, $limit) {
		global $conn;
		$longquery = sprintf("select Politicians.*, MATCH(Politicians.TopWords) AGAINST ('%s') as score from Politicians ORDER BY score DESC LIMIT 0, %u",$conn->real_escape_string($searchterm), $limit);
		return query($longquery);
	}
	
	if (!isset($_POST['searchterm'])) {
		if (isset($_SESSION['searchterm'])) {
			$_POST['searchterm'] = $_SESSION['searchterm'];
		} else {
			header('Location: index.php?entersearch');
		}
	}

	$guardianarticles = array();

	$guardianurl = sprintf('http://content.guardianapis.com/search?q=%s'.
		'&format=json&show-fields=headline%%2Cbody%%2Cstandfirst&show-references=all&api-key=%s',
		urlencode($_POST['searchterm']),
		$guardianapikey);
	$guardianresult = file_get_contents($guardianurl);
	$result = json_decode($guardianresult);
	if ($result->response->total > 0) {
		$guardianarticles = array_merge($guardianarticles,$result->response->results);
	}

	$guardianarticles = array_filter(array_map($array_map, $guardianarticles),$array_filter);
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
			<h1>Letter</h1>
		</div><!-- /header -->
		<div data-role="content" data-theme="d">
			<form method="post" action="./sendmessage.php" data-ajax="false">
				Category:
				<select onchange="$('#chosepolitician').load('./findpoliticians.php?depid=' + $(this).val() + '&searchterm=<?php echo(urlencode($_POST['searchterm'])); ?>', function() {$('#chosepolitician').trigger('create');});">
					<option value="0">Recommended</option><?php
	$departments = query("select DepartmentID, DepartmentName from Departments order by DepartmentName");
	foreach ($departments as $d) {
		echo("
					<option value=\"".$d[0]."\">".$d[1]."</option>");
	}
	?>
				</select>
				To:
				<fieldset data-role="controlgroup" style="height:10pc;overflow:auto;" id="chosepolitician">
					<?php
	$relpols = getRelatedPoliticians($_POST['searchterm'], 10);
	foreach ($relpols as $rp) {
		echo("
					<input type=\"checkbox\" name=\"".$rp[0]."\" id=\"".$rp[0]."\" />
					<label style=\"font-size:0.5pc;\" for=\"".$rp[0]."\">".$rp[3]."</label>");
	}
	?>
				</fieldset>
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
			<h2>Article Search Results</h2>
			<ul data-role="listview" data-inset="true" data-filter="true" id="articles-list">
				<?php foreach ($guardianarticles as $article) {
					echo('<li><a href="viewarticle.php?articleurl='.urlencode($article->id).'" data-panel="main" alt="'.htmlentities($article->fields->standfirst).'">'.$article->fields->headline.'</a></li>'.PHP_EOL);
				} ?>
			</ul>
		</div><!-- /content -->

	<?php include('footer.php'); ?>

	</div><!-- /page -->

</div>

</body>
</html>
