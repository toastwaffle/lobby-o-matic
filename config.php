<?php
    ini_set('display_errors','On');
    error_reporting(E_ALL);

    session_start();

    global $conn,$guardianapikey;
	$conn = new mysqli('localhost','yrswebuser','sndTDaEqDerGr643','yrstest');
	$guardianapikey = 'dmkvkaamsuthc484cy53xu3z';

    $messages = ''; // Used to display messages on every page.

    if (isset($_GET['loggedin'])) {
        $messages .= '<p class="success">You are now logged in.</p>';
    }
    if (isset($_GET['registered'])) {
        $messages .= '<p class="success">You have been registered. Please check your email to confirm your address.</p>';
    }
    if (isset($_GET['pleaselogin'])) {
        $messages .= '<p class="warning">Please log in to use the system.</p>';
    }
    if (isset($_GET['noconfirm'])) {
        $messages .= '<p class="warning">Sorry, we couldn\'t confirm your email. Please go to your settings to resend the confirmation email.</p>';
    }
    if (isset($_GET['confirmed'])) {
        $messages .= '<p class="success">You\'re email address has been confirmed.</p>';
    }
    if (isset($_GET['error'])) {
        $messages .= '<p class="error">An error occurred. Please try again later.</p>';
    }
    if (isset($_GET['entersearch'])) {
        $messages .= '<p class="error">Please enter a search term.</p>';
    }

    function shutdown() {
        global $conn;
        $conn->close();
    }

    register_shutdown_function('shutdown');

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

    function getBillText($url,&$pdfurl) {
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

        $pdfurl = $matches[0];

        downloadFile($matches[0],$basefilename.'.pdf');

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
        $stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www','bill','parliament','act','lords','commons','parliamentary','person','section','under','about','from','activity','activities','subsection','part','which','0','1','2','3','4','5','6','7','8','9','wikipedia','zeitgeist','north','south','east','west','margin','padding','edit');

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

    $array_map = function($item) {
        if ($item->fields->body == '<!-- Redistribution rights for this field are unavailable -->') {
            return null;
        }
        $item->fields->body = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $item->fields->body);
        $position = strpos($item->fields->body, '<div class="gu_advert">');
        if (($position !== False) && ($position > 0)) {
            $item->fields->body = substr($item->fields->body, 0, $position);
        }
        return $item;
    };

    $array_filter = function($item) {
        return ($item === null) ? false : true;
    };
?>