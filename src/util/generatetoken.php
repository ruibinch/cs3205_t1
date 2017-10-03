<?php
require '../composer/vendor/autoload.php';
use \Firebase\JWT\JWT;

function getToken($uid, $key) {
    //TODO: updateSecret()
    $secret = updateSecret($uid);
    $token = array(
        "exp" => time() + 3600,
        "secret" => $secret,
        "data" => array(
            "uid" => $uid
            )
    );
    return JWT::encode($token, $key);
}

?>