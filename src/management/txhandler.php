<?php
	//txhandler.php: helper script to retreive info from DB
		
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/jwt-admin.php';
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"], "dummykey");	
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		echo "Go home, you are drunk.";
		exit();
	}
?>

<?php
	sleep(1);
	if ($_POST['mode'] === "all") {
		echo "All";
	}
	
	if ($_POST['mode'] === "warn") {
		echo "Warn";
	}
?>