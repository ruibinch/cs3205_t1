<?php
require __DIR__ . '/../composer/vendor/autoload.php';
use \Firebase\JWT\JWT;

include_once $_SERVER['DOCUMENT_ROOT'].'/util/ssl.php';

class WebToken
{

    private static $serverurl = "http://cs3205-4-i.comp.nus.edu.sg/";

    /*
     * @param $admin_id
     * @param $key - secret key
     */
    static function getToken($admin_id)
    {
        $key = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['admin'];
        if (self::refreshSecret($admin_id)) {
            $secret = json_decode(ssl::get_content(self::$serverurl . "api/team1/admin/secret/" . $admin_id))->secret;
            $token = array(
                "exp" => time() + 3600,
                "secret" => $secret,
                "data" => array(
                    "admin_id" => $admin_id,
                )
            );
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
        $key = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['admin'];
        if ($token) {
            try {
                $decoded = JWT::decode($token, $key, array(
                    'HS256'
                ));
                if ($decoded->exp < time()) {
                    header("Location: /login.php?error=1");
                    die();
                } else {
                    $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/admin/secret/" . $decoded->data->admin_id));
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
     * @param $admin_id
     * @return true for success, false otherwise
     */
    static function refreshSecret($admin_id)
    {
        $secret = self::getSecret($admin_id);
        $string = bin2hex(random_bytes(20));
        while ($secret && strcmp($secret, $string) == 0)
            $string = bin2hex(random_bytes(20));
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/admin/secret/set/" . $admin_id . "/" . $string));
        if ($result->result == 1)
            return true;
        else
            return false;
    }

    static function getSecret($admin_id)
    {
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/admin/secret/" . $admin_id));
        if (isset($result->secret))
            return $result->secret;
        else
            return null;
    }
}
?>