<?php
	include('config.php');
	if (!isset($_SESSION['username'])) {
		if (isset($_GET['articleid'])) {
			$_SESSION['articleid'] = $_GET['articleid'];
		}
		if (isset($_GET['articleurl'])) {
			$_SESSION['articleurl'] = $_GET['articleurl'];
		}
		header('Location: login.php?redirect=viewarticle.php&pleaselogin');
	}
	
	if ((!isset($_GET['articleid'])) && (!isset($_GET['articleurl']))) {
		if (isset($_SESSION['articleid'])) {
			$_GET['articleid'] = $_SESSION['articleid'];
		} else if (isset($_SESSION['articleurl'])) {
			$_GET['articleurl'] = $_SESSION['articleurl'];
		} else {
			header('Location: '.$_SESSION['oldPage']);
		}
	}

	if (isset($_GET['articleid'])) {
		$query = sprintf('SELECT * FROM Articles WHERE id = %u',$conn->real_escape_string($_GET['articleid']));
		$result = $conn->query($query);
		if (!$result) {
			if (strpos($_SESSION['oldPage'], 'php?') !== False) {
				header('Location: '.$_SESSION['oldPage'].'&articlenotfound');
			} else {
				header('Location: '.$_SESSION['oldPage'].'?articlenotfound');
			}
		}
		$article = $result->fetch_assoc($result);
		$document = $article['body'];
		$title = $article['title'];
		$query = sprintf('SELECT * FROM Entities WHERE articleid = %u GROUP BY type ORDER BY relevance DESC',$article['id']);
		$result = $conn->query($query);
		$entityid = 0;
		$entitieslist = '';
		$first = true;
		$lasttype = '';
		while ($entity = $result->fetch_assoc()) {
			$entityid++;
			if ($entity['type'] != $lasttype) {
				$lasttype = $entity['type'];
				if (!$first) {
					$entitieslist .= '</ul><li>'.PHP_EOL;
				} else {
					$first = false;
				}
				$entitieslist .= '<li onclick="showsublist('.$entityid.');">'.$entitytype.' &gt;&gt;<ul id="entitysublist-'.$entityid.'" style="display: none;">'.PHP_EOL;
			}
			foreach ($entities as $entityname => $entityinfo) {
				$entityid++;
				$entitieslist .= '<li id="entity-'.$entityid.'"><a href="javascript:highlightentity('.$entityid.');">'.$entity['name'].' (Relevance Score: '.$entity['relevance'].')</a></li>'.PHP_EOL;
				$query = sprintf('SELECT * FROM EntityInstances WHERE entityid = %u',$entity['id']);
				$instanceresult = $conn->query($query);
				while ($instance = $instanceresult->fetch_assoc()) {
					$search = '/\b'.preg_quote(trim($instance['exact'])).'\b/';
					$replace = '<span class="highlighter-'.$id.'">'.trim($instance['exact']).'</span>';
					$document = preg_replace($search, $replace, $document);
				}
			}
		}
	} else if (isset($_GET['articleurl'])) {
		$guardianurl = sprintf('http://content.guardianapis.com/%s'.
			'?format=json&show-fields=headline%%2Cbody&api-key=%s',
			$_GET['articleurl'],
			$guardianapikey);
		$guardianresult = file_get_contents($guardianurl);
		$result = json_decode($guardianresult);
		if ($result->response->total < 1) {
			$title = $result->response->content->fields->headline;
			$document = $result->response->content->fields->body;

			$baseurl = 'http://api.opencalais.com/tag/rs/enrich';

			$text = substr($document,0,100000);

			$ch = curl_init($baseurl);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'x-calais-licenseID: nnjdz39esaj7uab2x4s7qwvu',
				'Content-Type: text/raw',
				'Content-Length: ' . strlen($text),
				'Accept: application/json'));
			$result = curl_exec($ch);
			curl_close($ch);

			$data = json_decode($result,true);

			unset($data['doc']);

			$information = array('entities'=>array());

			foreach ($data as $key => $value) {
				switch ($value['_typeGroup']) {
					case 'topics':
						break;
					case 'entities':
						$information['entities'][$value['_type']][$value['name']] = array(
							'instances'=>$value['instances'],
							'relevance'=>$value['relevance']);
						break;
				}
			}

			$uasort_function = function($a,$b) {
				if ($a['relevance'] > $b['relevance']) {
					return -1;
				} else if ($a['relevance'] < $b['relevance']) {
					return 1;
				} else {
					return 0;
				}
			};

			foreach (array_keys($information['entities']) as $key) {
				uasort($information['entities'][$key],$uasort_function);
			}
			$id = 0;
			$entitieslist = '';
			foreach($information['entities'] as $entitytype => $entities) {
				$id++;
				$entitieslist .= '<li onclick="showsublist('.$id.');">'.$entitytype.' &gt;&gt;<ul id="entitysublist-'.$id.'" style="display: none;">'.PHP_EOL;
				foreach ($entities as $entityname => $entityinfo) {
					$id++;
					$entitieslist .= '<li id="entity-'.$id.'"><a href="javascript:highlightentity('.$id.');">'.$entityname.' (Relevance Score: '.$entityinfo['relevance'].')</a></li>'.PHP_EOL;
					foreach ($entityinfo['instances'] as $instance) {
						$search = '/\b'.preg_quote(trim($instance['exact'])).'\b/';
						$replace = '<span class="highlighter-'.$id.'">'.trim($instance['exact']).'</span>';
						$document = preg_replace($search, $replace, $document);
					}
				}
				$entitieslist .= '</ul></li>'.PHP_EOL;
			}
		} else {
			if (strpos($_SESSION['oldPage'], 'php?') !== False) {
				header('Location: '.$_SESSION['oldPage'].'&articlenotfound');
			} else {
				header('Location: '.$_SESSION['oldPage'].'?articlenotfound');
			}
		}
	} else {
		header('Location: '.$_SESSION['oldPage']);
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
	<script type="text/javascript">
	  function showsublist(id) {
	    $("#entitysublist-"+id).toggle();
	  }
	  function highlightentity(id) {
	  	if ($("#entity-"+id).hasClass('highlighted')) {
	  		$('.highlighter-'+id).removeClass('highlighted');
	  		$("#entity-"+id).removeClass('highlighted');
	  		$("#entity-"+id).parent().show();
	  	} else {
	  		$('.highlighter-'+id).addClass('highlighted');
	  		$("#entity-"+id).addClass('highlighted');	
	  		$("#entity-"+id).parent().show();
	  	}
	  }
	</script>
	<style type="text/css">
		span.highlighted {
			border-top: 1px solid red;
			border-bottom: 1px solid red;
			background-color: #FCC;
		}
	</style>
</head> 
<body>

<div data-role="panel" data-id="main">
	<div data-role="page" id="main">

		<div data-role="header">
			<h1>Lobby-O-Matic</h1>
		</div><!-- /header -->

		<div data-role="content" data-theme="b">
			<h2><?php echo($article['title']); ?></h2>
			<h3>Matched Entities</h3>
			<p>Click to expand types, click on entity to highlight in text</p>
			<ul id="entity-list">
				<?php echo($entitieslist); ?>
			</ul>
			<h3>Article Text</h3>
			<?php echo($document); ?>
		</div><!-- /content -->

	<?php include('footer.php'); ?>

	</div><!-- /page -->

</div>

</body>
</html>
