<?php
	//txhandler.php: helper script to retreive info from DB
		
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/jwt-admin.php';
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/ssl.php';
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"]);	
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		echo "Go home, you are drunk.";
		exit();
	}
?>

<?php
	sleep(1);
	if ($_POST['mode'] === "all") {
        $dbURL = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/log/";
		
		if ($connection === FALSE) {
			echo "Database connection failed. Try again later.";
			exit();
		}
		
		$connection = ssl::get_content($dbURL);
		$decodeJS = json_decode($connection);
		//Check for empty table
		if (empty($decodeJS->logs)) {
			echo 'No Transaction Logs Available.';
			exit();
		}		
		echo '<table id="logTable">';
		echo "\t" . '<th>UserID</th><th>Time</th><th>Description</th>';
		
		foreach ($decodeJS->logs as &$value) {
			echo "\t" . '<tr id="Log' . $value->classification . '"><td>' . $value->uid . '</td><td>' . $value->time . '</td><td>' . $value->description . '</td></tr>';
		}
		echo '</table>';
	}
?>