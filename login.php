<?php
	include('config.php');
?>
<!DOCTYPE html> 
<html> 
	<head> 
	<title>Lobby-O-Matic</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="./resources/jquery.mobile-1.1.1.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.structure-1.1.1.css" />
	<link rel="stylesheet" href="./resources/jquery.mobile.theme-1.1.1.css" />
	<script src="./resources/jquery-1.7.1.min.js"></script>
	<script src="./resources/jquery.mobile-1.1.1.min.js"></script>
</head> 
<body> 

<div data-role="page">

	<div data-role="header">
		<h1>Lobby-O-Matic - Login</h1>
	</div><!-- /header -->

	<div data-role="content" data-theme="b">
		<p>Please log in or register below.</p>
		<form action="login.php" method="post">
			<p><label for="loginusername">Username: </label><input type="text" name="username" id="loginusername" /></p>
			<p><label for="loginpassword">Password: </label><input type="password" name="password" id="loginpassword" /></p>
			<input type="submit" data-icon="star" name="login" value="Log In" />
		</form>
		<br />
		<form action="login.php" method="post">
			<p><label for="regusername">Username: </label><input type="text" name="username" id="regusername" /></p>
			<p><label for="firstname">First Name: </label><input type="text" name="firstname" id="firstname" /></p>
			<p><label for="lastname">Last Name: </label><input type="text" name="lastname" id="lastname" /></p>
			<p><label for="email">Email: </label><input type="email" name="email" id="email" /></p>
			<p><label for="regpassword">Password: </label><input type="password" name="password" id="regpassword" /></p>
			<p><label for="repeat">Repeat Password: </label><input type="password" name="repeat" id="repeat" /></p>
			<input type="submit" data-icon="star" name="register" value="Register" />
		</form>
		<br />
	</div><!-- /content -->

<?php include('footer.php'); ?>

</div><!-- /page -->

</body>
</html>