<?php
    ini_set('display_errors','On');
    error_reporting(E_ALL);

    global $conn,$guardianapikey;
	$conn = new mysqli('toastwaffle.com','yrswebuser','sndTDaEqDerGr643','yrs2012');
	$guardianapikey = 'dmkvkaamsuthc484cy53xu3z';

    function downloadFile ($url, $path) {
        $newfname = $path;
        $file = fopen ($url, "rb");
        if ($file) {
            $newf = fopen ($newfname, "wb");
            if ($newf) {
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
                }
            }
        }

        if ($file) {
        fclose($file);
        }

        if ($newf) {
        fclose($newf);
        }
    }

    function getBillText($url, &$pdfurl) {
        $documentsurl = str_replace('.html', '/documents.html', $url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $documentsurl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $documentshtml = curl_exec($ch);
        curl_close($ch);

        $matches = array();
        preg_match('/http:\/\/www.publications.parliament.uk\/.*?.pdf/', $documentshtml, $matches);

        $basefilename = '/tmp/bill'.(string)time();

				$pdfurl = $matches[0].$basefilename.'.pdf';
        downloadFile($matches[0], $pdfurl);

        system('pdftotext '.$basefilename.'.pdf');

        unlink($basefilename.'.pdf');

        $billtext = iconv("UTF-8", "ISO-8859-1//TRANSLIT", file_get_contents($basefilename.'.txt'));
        unlink($basefilename.'.txt');

        $position = strpos(strtolower($billtext), 'ordered to be printed');
        if ($position !== false) {
            $billtext = substr($billtext,0,$position);
        }

        $position = strpos(strtolower($billtext), 'ordered, by the house of commons');
        if ($position !== false) {
            $billtext = substr($billtext,0,$position);
        }

        return($billtext);
    }

    function query($querystring) {
        global $conn;
        $result = $conn -> query($querystring, MYSQLI_USE_RESULT);
        $x = array();
        if ($conn -> affected_rows == - 1) {
            while ($a = $result -> fetch_row()) {
                $x[] = $a;
            }
            $result -> close();
        }
        return $x;
    }

    function extractCommonWords($string,$count){
        $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www','bill','parliament','act','lords','commons','parliamentary','person','section','under','about','from','activity','activities','subsection','part','which');

        $string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
        $string = trim($string); // trim the string
        $string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
        $string = strtolower($string); // make it lowercase

        preg_match_all('/\b.*?\b/i', $string, $matchWords);
        $matchWords = $matchWords[0];

        foreach ( $matchWords as $key=>$item ) {
            if ( $item == '' || in_array(strtolower($item), $stopWords) || strlen($item) <= 3 ) {
                unset($matchWords[$key]);
            }
        }   
        $wordCountArr = array();
        if ( is_array($matchWords) ) {
            foreach ( $matchWords as $key => $val ) {
                $val = strtolower($val);
                if ( isset($wordCountArr[$val]) ) {
                    $wordCountArr[$val]++;
                } else {
                    $wordCountArr[$val] = 1;
                }
            }
        }
        arsort($wordCountArr);
        $wordCountArr = array_slice($wordCountArr, 0, $count);
        return $wordCountArr;
    }
?>
