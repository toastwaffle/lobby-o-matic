<?php
	include('config.php');
	if (!((isset($_GET['u'])) && (isset($_GET['key'])))) {
		header('Location: index.php?noconfirm');
	} else {
		$query = sprintf("SELECT firstname,lastname,email FROM Users WHERE username = '%s'",$conn->real_escape_string($_GET['u']));
		$result = $conn->query($query);
		if (!$result) {
			header('Location: index.php?error');
			error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
		} else {
			$row = $result->fetch_assoc();
			$testhash = hash('sha512',$row['firstname'].$row['lastname'].$row['email']);
			if ($testhash == $_GET['key']) {
				$query = sprintf("UPDATE Users SET confirmed=1 WHERE username = '%s'",$conn->real_escape_string($_GET['u']));
				$result = $conn->query($query);
				if (!$result) {
					header('Location: index.php?error');
					error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
				} else {
					header('Location: index.php?confirmed');
				}
			} else {
				header('Location: index.php?noconfirm');
			}
		}
	}