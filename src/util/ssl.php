<?php
/* SSL setup
* How to use. E.g.
	// Initialise cURL
	$resultDel = curl_init();
	//Set url for cURL
	curl_setopt($resultDel, CURLOPT_URL, $url);
	//Use setSSL() to setup curl with SSL credentials.
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/ssl.php';
	setSSL($resultDel);
	
	//Execute curl
	curl_exec($resultDel);
	echo curl_error($resultDel);
	curl_close($resultDel);
*/
$certFile = "/usr/keys/team1-cert.pem";
$keyFile = "/usr/keys/team1-key.pem";
$caInfo = "/usr/keys/cacert.crt";
	

/*
*Use this method to set necessary SSL credentials for cURL reference passed into this method.
*
*@param $curl the curl variable for calling api
*@return $curl 
*/
function setSSL($curl)
{
	global $certFile, $keyFile, $caInfo;
	//curl_setopt($curl, CURLOPT_VERBOSE, 0);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); 
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1); 
	//Set ssl key here
	curl_setopt($curl, CURLOPT_SSLKEY, $keyFile);
	// The --cert option
	curl_setopt($curl, CURLOPT_SSLCERT, $certFile);
	curl_setopt($curl, CURLOPT_CAINFO, $caInfo);
	//curl_setopt($curl, CURLOPT_CAPATH, '/home/sadm/keys/ssl/');
	
	return $curl;
}
?>
	