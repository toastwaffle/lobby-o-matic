<?php
    header('Content-Type: text/plain');

    include('config.php');

    echo(getBillText('http://services.parliament.uk/bills/2012-13/antarctic.html'));
?>