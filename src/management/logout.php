<?php
	//logout.php: Destroys all session and logout.
	
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/jwt-admin.php';
	
	// TODO: change the dummy key here to the real key
	$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
	WebToken::refreshSecret($result->a_id);
	setcookie("jwt", "", time()-3600, "/", null, true, true);
		
	//Adapted from: http://php.net/manual/en/function.session-destroy.php
	
	// Unset all of the session variables.
	$_SESSION = array();
	
	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
	}
	
	//Finally, destroy the session.
	session_destroy();
	header("Location: /index.php");
	exit();
?>