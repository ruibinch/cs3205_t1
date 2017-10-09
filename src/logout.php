<?php
include_once 'util/jwt.php';

// TODO: change the dummy key here to the real key
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
WebToken::refreshSecret($result->uid);
setcookie("jwt", "", time()-3600, "/", null, true, true);

header("Location: /login.php");
die();

?>