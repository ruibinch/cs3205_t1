<?php
	//txhistory.php: shows transaction history. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../index.php");
		exit();
	}
?>

<h1>Transaction History</h1>
<h3>This page is a stub. Intention is to search transaction by type, date/time, user (if possible).</h3>