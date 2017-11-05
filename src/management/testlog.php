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
		
	$uid = 2;
	$classification = "Error";
	$description = "PATIENT: Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. Server messed up. ";	
	
	Log::recordTX($uid, $classification, $description);
?>
