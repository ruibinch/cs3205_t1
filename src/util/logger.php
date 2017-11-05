<?php
	//logger.php: Handler to send transaction logs to DB
	
class Log {
	static function recordTX($uid, $classification, $description) {
		$dbURL = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/log/create";		
		$timeStamp = round(microtime(true) * 1000);
		
		$logToDB = array(
			"uid" => $uid,
			"description" => $description,
			"classification" => $classification,
			"time" => $timeStamp,
			"api"  => "Team1"
		);
		
		$logToDB_json = json_encode($logToDB);
		
		$ch = curl_init($dbURL); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $logToDB_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($logToDB_json))
		);
		
		//Establish connection to DB server and get result.
		$connection = @curl_exec($ch);
				
		//Ignore Result.
	}
}
?>