<?php
	//process.php: Process login.
	
	//TODO: IMPLEMENT DATABASE LOGIN....
	//WARNING: THESE ARE THE DEFAULT LOGIN DETAILS. THEY MUST BE REMOVED WHEN IT IS THE FINAL VERSION FOR SUBMISSION
	
	session_start();
	
	$username = "team1";
	$password = '$2y$10$ysODL/dUnmJaSHqLp4gz.uVQLFmExwWi1yRO/DYA4S6SxHg6Y0L7u';  //plaintext: mainecoon
	
	//Local login without DB.. will remove it... soon.
	if ($_POST['mgmt-username'] === $username && password_verify($_POST['mgmt-password'], $password)) {
		$_SESSION['loggedin'] = $username;
		header("location: console.php");
		exit();
	}
	
	//DB Login..
	$result = file_get_contents('http://cs3205-4-i.comp.nus.edu.sg/api/team1/admin/' . $_POST['mgmt-username']);
	
	$decode = json_decode($result);
	
	if (isset($decode->username) && password_verify($_POST['mgmt-password'], $decode->password)) {
		$_SESSION['loggedin'] = $decode->username;
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
