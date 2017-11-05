<?php
require '../composer/vendor/autoload.php';
use \Firebase\JWT\JWT;
include_once 'jwt.php';

$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];

// test secret
$result = WebToken::getSecret(7);
echo 'secret before for uid 7: ' . $result . "<br>\n";
WebToken::refreshSecret(7);
$result = WebToken::getSecret(7);
echo 'secret after for uid 7: ' . $result . "<br>\n";

// test generate
$token = WebToken::getToken(5, false, "dummy_key");
echo "jwt is: " . $token . "<br>\n";
echo "verified token: " . WebToken::verifyToken($token, "dummy_key") . "<br>\n";

?>