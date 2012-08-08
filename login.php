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
		<h2>Please log in or register below.</h2>
		<div data-role="container" data-corners="true">
			<h3>Log In</h3>
			<form action="login.php" method="post">
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="loginusername">Username: </label><input type="text" name="username" id="loginusername" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="loginpassword">Password: </label><input type="password" name="password" id="loginpassword" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<input type="submit" data-icon="star" name="login" value="Log In" />
				</div>
			</form>
		</div>
		<br />
		<div data-role="container" data-corners="true">
			<h3>Register</h3>
			<form action="login.php" method="post">
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="regusername">Username: </label><input type="text" name="username" id="regusername" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="firstname">First Name: </label><input type="text" name="firstname" id="firstname" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="lastname">Last Name: </label><input type="text" name="lastname" id="lastname" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="email">Email: </label><input type="email" name="email" id="email" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="regpassword">Password: </label><input type="password" name="password" id="regpassword" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="repeat">Repeat Password: </label><input type="password" name="repeat" id="repeat" />
				</div>
				<div data-role="fieldcontain" class="ui-hide-label">
					<input type="submit" data-icon="star" name="register" value="Register" />
				</div>
			</form>
		</div>
	</div><!-- /content -->

<?php include('footer.php'); ?>

</div><!-- /page -->

</body>
</html>