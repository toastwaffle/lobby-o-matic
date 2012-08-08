<?php
	$text = <<<TEXT
Alan Turing (Statutory Pardon) Bill [HL]

A

BILL
TO

Give a statutory pardon to Alan Mathison Turing for offences under section 11
of the Criminal Law Amendment Act 1885 of which he was convicted on 31
March 1952.

B

by the Queen's most Excellent Majesty, by and with the advice and
consent of the Lords Spiritual and Temporal, and Commons, in this present
Parliament assembled, and by the authority of the same, as follows:--

1

E IT ENACTED

Statutory Pardon of Alan Mathison Turing
(1)

(2)

2

Alan Mathison Turing, who was born on 23 June 1912 and died on 8 June 1954,
and who was convicted of offences under section 11 of the Criminal Law
Amendment Act 1885 (gross indecency between men) at the Quarter Sessions
at Knutsford in Cheshire on 31 March 1952, is to be taken to be pardoned for
those offences.

5

This Act does not affect any conviction or sentence or give rise to any right,
entitlement or liability, and does not affect the prerogative of mercy.
Short title and extent

(1)

This Act may be cited as the Alan Turing (Statutory Pardon) Act 2012.

(2)

This Act extends to England and Wales.

HL Bill 40

10

55/2

Alan Turing (Statutory Pardon) Bill [HL]

A

BILL
To give a statutory pardon to Alan Mathison Turing for offences under section
11 of the Criminal Law Amendment Act 1885 of which he was convicted on 31
March 1952.

Lord Sharkey
TEXT;

	$baseurl = 'http://api.opencalais.com/tag/rs/enrich';

	$ch = curl_init($baseurl);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, substr($text,0,100000));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'x-calais-licenseID: nnjdz39esaj7uab2x4s7qwvu',
		'Content-Type: text/raw',
		'Content-Length: ' . strlen($text),
		'Accept: application/json'));
	$result = curl_exec($ch);
	curl_close($ch);

	$data = json_decode($result,true);

	$document = $data['doc']['info']['document'];

	unset($data['doc']);

	$information = array(
		'topics'=>array(),
		'entities'=>array());

	foreach ($data as $key => $value) {
		switch ($value['_typeGroup']) {
			case 'topics':
				$information['topics'][$value['categoryName']] = $value['score'];
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

	//asort($information['topics']);
	foreach (array_keys($information['entities']) as $key) {
		uasort($information['entities'][$key],$uasort_function);
	}
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
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
<div class="document">
	<h2>Matched Topics</h2>
	<ul id="topics-list">
	<?php
		foreach($information['topics'] as $topic => $score) {
			echo('<li>'.$topic.' (Relevance Score: '.$score.')</li>'.PHP_EOL);
		}
	?>
	</ul>
	<h2>Matched Entities</h2>
	<ul id="entity-list">
	<?php
		$id = 0;
		foreach($information['entities'] as $entitytype => $entities) {
			$id++;
			echo('<li onclick="showsublist('.$id.');">'.$entitytype.' &gt;&gt;<ul id="entitysublist-'.$id.'" style="display: none;">'.PHP_EOL);
			foreach ($entities as $entityname => $entityinfo) {
				$id++;
				echo('<li id="entity-'.$id.'"><a href="javascript:highlightentity('.$id.');">'.$entityname.' (Relevance Score: '.$entityinfo['relevance'].')</a></li>'.PHP_EOL);
				foreach ($entityinfo['instances'] as $instance) {
					$search = '/\b'.preg_quote(trim($instance['exact'])).'\b/';
					$replace = '<span class="highlighter-'.$id.'">'.trim($instance['exact']).'</span>';
					$document = preg_replace($search, $replace, $document);
				}
			}
			echo('</ul></li>'.PHP_EOL);
		}
	?>
	</ul>
	<p><?php echo($document); ?></p>
</div>