<?php 
include_once 'otl.php';

$onetime = OneTimeToken::generateToken(3, "/dummy.jpg", "dummytoken");
echo $onetime."<br>\n";
echo "check if token exists: " . json_encode(OneTimeToken::getToken($onetime)) . "<br>\n";
OneTimeToken::deleteToken($onetime);
echo "check if token exists after deletion: " . json_encode(OneTimeToken::getToken($onetime)) . "<br>\n";

?>