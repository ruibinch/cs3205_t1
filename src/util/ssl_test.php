<?php
include_once 'ssl.php';

/*
* This component tests the curl method on ssl.php
*/
echo "Testing curl\n";
$url = "https://cs3205-4-i.comp.nus.edu.sg/api/team1/user/username/bob99";
echo "URL : ".$url."\n";
// Initialise cURL
$curl = curl_init();
//Set url for cURL
curl_setopt($curl, CURLOPT_URL, $url);
//Use setSSL() to setup curl with SSL credentials.
ssl::setSSL($curl);
//Execute curl
curl_exec($curl);
//echo curl_error($curl);
curl_close($curl);
$decode = json_decode($curl);		
echo $decode;
echo "\n\n";


/*
* This component tests the get_content method on ssl.php
*/
echo "Testing get_content\n";
$url = "https://cs3205-4-i.comp.nus.edu.sg/api/team1/user/username/bob99";
echo "URL : ".$url."\n";
// Initialise cURL
$curl = ssl::get_content($url);
echo $curl;
echo "\n\n";


/*
* This component tests the curl method on ssl.php WITH basic auth included.
*/
echo "Testing curl WITH basic auth\n";
$url = "https://cs3205-4-i.comp.nus.edu.sg/test/api/team1/user/username/bob99";
echo "URL : ".$url."\n";
// Initialise cURL
$curl = curl_init();
//Set url for cURL
curl_setopt($curl, CURLOPT_URL, $url);
//Use setSSL() to setup curl with SSL credentials.
ssl::setSSL($curl);
//Set basic auth header
$headers = ['Authorization: dGVhbTE6dGVhbTFMb3Zlc0Nvcmdp'];
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//Execute curl
curl_exec($curl);
//echo curl_error($curl);
curl_close($curl);
$decode = json_decode($curl);		
echo $decode;
echo "\n";

?>