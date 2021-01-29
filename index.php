<?php
// Too lazy to use for this example, 
// but if you're here then I guess you know
// what to do.
session_start();

?>

<!DOCTYPE html>
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="assets/style/style.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	</head>

	<body>
		<div class="loginRow">
			<p>No error handling for wrong password in this example</p>
			<p>
				Your account will get locked for 30 minutes after failing 3 times. <br /><br />
				You can find more vehicle info in your console. This example will only <br />
				show you model year, model name, and an image if your cars configuration <br /> <br />
				Mainly supports the Porsche Taycan as that's what I have. Hard to test for others without owning them.
			</p>
			<form action="authenticate.php" method="post" class="loginForm">
				<input class="form-control" id="email" type="email" required name="email" placeholder="Email" />
				<input class="form-control" id="password" type="password" required name="password" placeholder="Password" />
				
				<input class="button" id="login" type="submit" name="submit" value="Login" />
			</form>
		</div>

		<div class="infoRow">
			<h2 id="loadingInfo">Loading...</h2>
			<h1 id="modelDescription">Model year, model name</h1>
			<img id="modelImage" src="" />
		</div>

		<script type="text/javascript" src="assets/js/script.js"></script>
	</body>
</html>