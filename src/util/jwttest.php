<?php
require '../composer/vendor/autoload.php';
use \Firebase\JWT\JWT;
include_once 'jwt.php';

$serverurl = "http://172.25.76.76/";

//test secret
$result = WebToken::getSecret(3);
echo 'secret before for uid 3: '.$result."<br>\n";
WebToken::refreshSecret(3);
$result = WebToken::getSecret(3);
echo 'secret after for uid 3: '.$result."<br>\n";

//test generate
$token = WebToken::getToken(3, "dummy_key");
echo "verified token: ".WebToken::verifyToken($token, "dummy_key")."<br>\n";

?>