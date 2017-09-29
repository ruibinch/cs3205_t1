<?php
	//process.php: Process login.
	
	//TODO: IMPLEMENT DATABASE LOGIN....
	//WARNING: THESE ARE THE DEFAULT LOGIN DETAILS. THEY MUST BE REMOVED WHEN IT IS THE FINAL VERSION FOR SUBMISSION
	
	session_start();
	
	$username = "team1";
	$password = '$2y$10$NHpcPdotapEtq5t2HR.iV.OVHzV8oCymH9n3Fwhnh0CYP9xGE6oG6';  //plaintext: corgi
	
	if ($_POST['mgmt-username'] === $username && password_verify($_POST['mgmt-password'], $password)) {
		$_SESSION['loggedin'] = $username;
		header("location: console.php");
		exit();
	} else {
		$_SESSION = array();
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
		);
		}
		session_destroy();
		header("Location: ../login.php?to=console&err=1");
	}
?>
