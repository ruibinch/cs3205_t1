<?php

include_once 'ssl.php';

class CSRFToken
{

    private static $serverurl;

    /*
     * @param $uid
     * @param $description
     * @return a token expiring in one hour
     */
    static function generateToken($uid, $description)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $string = bin2hex(random_bytes(20));
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/csrf/create/" . $string . "/" . $uid . "/" . (time()+3600) . "/" .$description));
        if ($result->result === false)
            return generateToken($uid, $description);
        return $string;
    }

    /*
     * @param $token
     * @return true if the token is found and deleted, false if it fails
     */
    static function deleteToken($token)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $result = self::getToken($token);
        if (isset($result->result) && ! ($result->result))
            return false;
        else {
            $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/csrf/delete/" . $token));
            if ($result->result == 1)
                return true;
            else
                return false;
        }
    }

    /*
     * @param $token
     */
    static function getToken($token)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/csrf/" . $token));
        return $result;
    }
}
?>