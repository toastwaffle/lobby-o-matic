<?php
	include('config.php');
	require('./resources/PasswordHash.php');

	$hasher = new PasswordHash(10, false);

	if (isset($_POST['login'])) {
		$query = sprintf("SELECT username,id,firstname,lastname,email,password FROM Users WHERE username = '%s'",
			$conn->real_escape_string($_POST['username']));
		$result = $conn->query($query);
		if (!$result) {
			$messages .= '<p class="error">An error occurred. Please try again later.</p>';
			error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
		} else {
			if ($result->num_rows !== 1) {
				$messages .= '<p class="error">Sorry, could not log you in. Please try again.</p>';
			} else {
				$row = $result->fetch_assoc();
				if ($hasher->CheckPassword($_POST['password'],$row['password'])) {
					unset($row['password']);
					foreach($row as $key => $value) {
						$_SESSION[$key] = $value;
					}
					header('Location: '.$_POST['redirect'].'?loggedin');					
				} else {
					$messages .= '<p class="error">Sorry, could not log you in. Please try again.</p>';
				}
			}
		}
	}
	if (isset($_POST['register'])) {
		$continue = true;
		if ($_POST['password'] != $_POST['repeat']) {
			$continue = false;
			$messages .= '<p class="error">Passwords do not match, please try again.</p>';
		}
		if (filter_var($_POST['email'],FILTER_VALIDATE_EMAIL) === false) {
			$continue = false;
			$messages .= '<p class="error">Invalid email address, please try again.</p>';
		}
		$query = sprintf("SELECT id FROM Users WHERE username = '%s'",
			$conn->real_escape_string($_POST['username']));
		$result = $conn->query($query);
		if (!$result) {
			$messages .= '<p class="error">An error occurred. Please try again later.</p>';
			error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
		} else {
			if ($result->num_rows > 0) {
				$continue = false;
				$messages .= '<p class="error">Username is already in use, please choose another.</p>';
			}
		}
		$query = sprintf("SELECT id FROM Users WHERE email = '%s'",
			$conn->real_escape_string($_POST['email']));
		$result = $conn->query($query);
		if (!$result) {
			$messages .= '<p class="error">An error occurred. Please try again later.</p>';
			error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
		} else {
			if ($result->num_rows > 0) {
				$continue = false;
				$messages .= '<p class="error">Email address is already in use, <a href="recoveraccount.php">recover account?</a></p>';
			}
		}
		if ($continue) {
			$query = sprintf("INSERT INTO Users (username,firstname,lastname,email,password) VALUES ('%s','%s','%s','%s','%s')",
				$conn->real_escape_string($_POST['username']),
				$conn->real_escape_string($_POST['firstname']),
				$conn->real_escape_string($_POST['lastname']),
				$conn->real_escape_string($_POST['email']),
				$hasher->HashPassword($_POST['password']));
			$result = $conn->query($query);
			if (!$result) {
				$messages .= '<p class="error">An error occurred. Please try again later.</p>';
				error_log('MySQL Error: '.$conn->error.PHP_EOL,3,'/home/samuel/lobbyomatic.log');
			} else {
				$confirmlink = urlencode('http://www.toastwaffle.com/lobby-o-matic/confirm.php?u='.$_POST['username'].'&key='.hash('sha512', $_POST['firstname'].$_POST['lastname'].$_POST['email']));
				$emailtext = <<<EMAILTEXT
Dear {$_POST['firstname']} {$_POST['lastname']},

Thanks for registering for Lobby-O-Matic. To complete your 
registration, please click on the following link, or copy and paste 
it into the address bar of your browser.

{$confirmlink}

Thanks,

The Lobby-O-Matic team.
EMAILTEXT;
				mail($_POST['email'], '[Lobby-O-Matic] Confirm Email Address', $emailtext, 'From: noreply@toastwaffle.com');
				unset($_POST['password']);
				foreach($_POST as $key => $value) {
					$_SESSION[$key] = $value;
				}
				$_SESSION['id'] = $conn->insert_id;
				header('Location: '.$_POST['redirect'].'?registered');
			}
		}
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
	<script type="text/javascript">
		$(document).bind("mobileinit", function(){
			 $.mobile.ignoreContentEnabled = true;
		});
	</script>
	<script type="text/javascript" src="./resources/jquery.mobile.splitview.js"></script>
	<script type="text/javascript" src="./resources/jquery.mobile.js"></script>
	<script type="text/javascript" src="./resources/iscroll-wrapper.js"></script>
	<script type="text/javascript" src="./resources/iscroll.js"></script>
</head> 
<body> 


<div data-role="panel" data-id="menu">
	<div data-role="page" id="menu">
		<div data-role="content">	
			<?php echo($messages); ?>
			<h2>Please log in or register below.</h2>
		</div>
	</div>
</div>

<div data-role="panel" data-id="main">
<div data-role="page" id="main" data-ajax="false">

	<div data-role="header">
		<h1>Lobby-O-Matic - Login</h1>
	</div><!-- /header -->

	<div data-role="content" data-theme="b" style="padding-bottom: 200px;">
		<div data-role="container" data-corners="true">
			<h3>Log In</h3>
			<form action="login.php" method="post">
				<?php
					if (isset($_GET['redirect'])) {
						echo('<input type="hidden" name="redirect" value="'.$_GET['redirect'].'" />'.PHP_EOL);
					} else {
						echo('<input type="hidden" name="redirect" value="index.php" />'.PHP_EOL);						
					}
				?>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="loginusername">Username: </label><input type="text" name="username" id="loginusername" placeholder="Username" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="loginpassword">Password: </label><input type="password" name="password" id="loginpassword" placeholder="Password" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<input type="submit" data-icon="star" name="login" value="Log In" data-ajax="false" />
				</div>
			</form>
		</div>
		<br />
		<div data-role="container" data-corners="true">
			<h3>Register</h3>
			<form action="login.php" method="post">
				<?php
					if (isset($_GET['redirect'])) {
						echo('<input type="hidden" name="redirect" value="'.$_GET['redirect'].'" />'.PHP_EOL);
					} else {
						echo('<input type="hidden" name="redirect" value="index.php" />'.PHP_EOL);						
					}
				?>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="regusername">Username: </label><input type="text" name="username" id="regusername" placeholder="Username" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="firstname">First Name: </label><input type="text" name="firstname" id="firstname" placeholder="First Name" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="lastname">Last Name: </label><input type="text" name="lastname" id="lastname" placeholder="Last Name" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="email">Email: </label><input type="email" name="email" id="email" placeholder="Email" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="regpassword">Password: </label><input type="password" name="password" id="regpassword" placeholder="Password" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="repeat">Repeat Password: </label><input type="password" name="repeat" id="repeat" placeholder="Repeat Password" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<input type="submit" data-icon="star" name="register" value="Register" data-ajax="false" />
				</div>
			</form>
		</div>
	</div><!-- /content -->

<?php include('footer.php'); ?>

</div><!-- /page -->
</div>

</body>
</html>