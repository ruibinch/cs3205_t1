<?php
require __DIR__ . '/../composer/vendor/autoload.php';
use \Firebase\JWT\JWT;
include_once 'ssl.php';

class WebToken
{

    private static $serverurl;
    
    /*
     * @param $uid
     * @param $key - secret key
     */
    static function getToken($uid, $istherapist)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        if (self::refreshSecret($uid)) {
            $secret = json_decode(ssl::get_content(self::$serverurl . "api/team1/user/secret/" . $uid))->secret;
            $token = array(
                "exp" => time() + 3600,
                "secret" => $secret,
                "data" => array(
                    "uid" => $uid,
                    "istherapist" => $istherapist // bool
                )
            );
            $key = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['normal'];
            return JWT::encode($token, $key);
        } else {
            throw new Exception('Fail to update secret');
        }
    }

    /*
     * @param $token - token found in cookie
     * @param $key - secret key
     */
    static function verifyToken($token)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        if ($token) {
            try {
                $key = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['normal'];
                $decoded = JWT::decode($token, $key, array(
                    'HS256'
                ));
                if ($decoded->exp < time()) {
                    header("Location: /login.php?error=1");
                    die();
                } else {
                    $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/user/secret/" . $decoded->data->uid));
                    if (strcmp($result->secret, $decoded->secret) != 0) {
                        /*
                         * Possile cause: logout, login somewhere else, change psw
                         */
                        header("Location: /login.php?error=2");
                        die();
                    }
                    return $decoded->data;
                }
            } catch (Exception $e) {
                /*
                 * This token cannot be decoded.
                 * The signature cannot be verified.
                 */
                echo $e;
                header("Location: /login.php?error=3");
                die();
            }
        } else {
            /*
             * Token is not present.
             */
            header("Location: /login.php?error=4");
            die();
        }
    }

    /*
     * @param $uid
     * @return true for success, false otherwise
     */
    static function refreshSecret($uid)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $secret = self::getSecret($uid);
        $string = bin2hex(random_bytes(20));
        while ($secret && strcmp($secret, $string) == 0)
            $string = bin2hex(random_bytes(20));
            $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/user/secret/set/" . $uid . "/" . $string));
        if ($result->result == 1)
            return true;
        else
            return false;
    }

    static function getSecret($uid)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/user/secret/" . $uid));
        if (isset($result->secret))
            return $result->secret;
        else
            return null;
    }
}
?>