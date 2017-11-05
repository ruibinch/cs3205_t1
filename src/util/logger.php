<?php
	//logger.php: Handler to send transaction logs to DB
	include_once 'ssl.php';
	
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
				
		//Establish connection to DB server and get result.
		@ssl::post_content($dbURL, $LogToDB_json, array('Content-Type: application/json', 'Content-Length: ' . strlen($LogToDB_json)));
				
		//Ignore Result.
	}
}
?>