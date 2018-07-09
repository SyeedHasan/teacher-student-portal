<!DOCTYPE html>
<html lang="en">

<head>
	<title>Student Sign Up</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" type="image/png" sizes="96x96" href="assets/images/icons/favicons/icons.png">
	<link rel="stylesheet" type="text/css" href="assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/fonts/iconic/css/material-design-iconic-font.min.css">
	<link rel="stylesheet" type="text/css" href="assets/css/main.css">
</head>

<body>

	<div class="mainDiv">
		<div class="loginContainer">
			<div class="loginWrapper">
				<form class="loginForm">
					<span class="loginForm-title p-b-26">
						Sign up
					</span>
					<!-- <span class="loginForm-title p-b-48">
						<i class="zmdi zmdi-font"></i>
					</span> -->
					<div class="inputWrapper">
						<input class="inputElement" type="text" name="firstName" required>
						<span class="focus-inputElement" data-placeholder="First Name"></span>
					</div>

					<div class="inputWrapper">
						<input class="inputElement" type="text" name="lastName" required>
						<span class="focus-inputElement" data-placeholder="Last Name"></span>
					</div>
					
					<div class="inputWrapper">
						<input class="inputElement" type="text" name="userName" required>
						<span class="focus-inputElement" data-placeholder="User Name"></span>
					</div>

					<div class="inputWrapper">
						<input class="inputElement" type="email" name="email" required>
						<span class="focus-inputElement" data-placeholder="Email"></span>
					</div>

					<div class="inputWrapper" id="lastInput">
						<span class="btn-show-pass">
							<i class="zmdi zmdi-eye"></i>
						</span>
						<input class="inputElement" type="password" minlength="6" maxlength="15" name="pass" required>
						<span class="focus-inputElement" data-placeholder="Password"></span>
					</div>

					<div class="cont-button">
						<div class="bg">
							<input class="loginButton" type="submit" value="Sign Up">
						</div>
					</div>

					<div class="signupText">
						<span class="txt1">
							Already have an account?
						</span>

						<a class="txt2" href="login.php" id="signupLink">
							Log In
						</a>
					</div>

				</form>
			</div>
		</div>

	</div>

	<script src="assets/js/main.js"></script>

</body>

</html>