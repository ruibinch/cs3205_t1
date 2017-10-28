<?php
	//login-validation-admin.php: Process login.
	//TODO: Implement JWT
	//TODO: Implement Challenge-Response
	
	include_once 'jwt-admin.php';
	
	// TODO: store the jwt secret key to some place safe
    $dummy_key = "dummykey";

    session_start();

	//DB Login..
	$result = @file_get_contents('http://172.25.76.76/api/team1/admin/' . $_POST['username']);
	
	if ($result === FALSE) {
		header("location: ../index.php");
		exit();
	}
	
    $decode = json_decode($result);
	
	if (isset($decode->username)) {
		//TODO: change the dummy key here to the real key
		setcookie("jwt", WebToken::getToken($decode->admin_id, $dummy_key),time()+3600, "/", null, true, true);
		$_SESSION['loggedin'] = $decode->username;
		header("location: /management/console.php");
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
		header("Location: /login.php?to=console&err=1");
		exit();
    }
?>
