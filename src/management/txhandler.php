<?php
	//txhandler.php: helper script to retreive info from DB
	
	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
?>

<?php
	
?>