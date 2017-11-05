<?php
	
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/logger.php';
	
	/*
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		echo $_POST['lol'] . "<br/>";
		$newPassword = password_hash($_POST['lol'], PASSWORD_BCRYPT);
		echo $newPassword . "<br/>";
		echo substr($newPassword, 0, 29) . "<br/>";
		echo hash('SHA256', $newPassword) . "<br/>";
	}
	
	//Values: Info, Warning, Error
	*/
		
	$uid = 1;
	$classification = "Info";
	$description = "PATIENT: Suspected Hacking Attempt";	
	
	Log::recordTX($uid, $classification, $description);
?>
