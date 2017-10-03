<?php 

require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

/*
 * @param $token - token found in cookie
 * @param $key - secret key 
 */
function checkJWT($cookie, $key) {
    $token = $cookie["token"];
    if ($token) {
        try {
            $decoded = JWT::decode($token, base64_decode(strtr($key, '-_', '+/')));
            if ($decoded->exp > time()) {
                header("Location: /login.php");
                die();
            } else {
                $curl = curl_init();
                //TODO: save server url to somewhere else
                curl_setopt($curl, CURLOPT_URL, $serverurl.$decoded->data->username);
                curl_setopt($curl, CURLOPT_PORT , 80); 
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($curl);
                if (strcmp($result->secret, $decoded->secret) != 0) {
                    /*
                     * Possile cause: logout, login somewhere else, change psw
                     */
                    header("Location: /login.php");
                    die();
                }
                return $decoded->data;
            }
        } catch (Exception $e) {
            /*
             * This token cannot be decoded.
             * The signature cannot be verified.
             */
            header("Location: /login.php");
            die();
        }
    } else {
        /*
         * Token is not present.
         */
        header("Location: /login.php");
        die();
    }
}

?>