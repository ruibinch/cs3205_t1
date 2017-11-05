<?php
include_once 'ssl.php';

class OneTimeToken
{

    private static $serverurl;

    /*
     * @param $uid
     * @param $filePath - relative path of the file
     * @param $CSRFToken
     */
    static function generateToken($uid, $filePath, $CSRFToken, $type)
    {
        self::$serverurl = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'];
        $string = bin2hex(random_bytes(20));
        $data = (object) null;
        $data->uid = $uid;
        $data->filepath = $filePath;
        $data->dataType = $type;
        $data->token = $string;
        $data->csrf = $CSRFToken;
        $header = ['Content-Type: application/json'];
        $result = json_decode(ssl::post_content(self::$serverurl . "api/team1/otl/create/", json_encode($data), $header));
        //if (!isset($result->result) || $result->result != 1)
            //return self::generateToken($uid, $filePath, $CSRFToken, $type);
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
            $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/otl/delete/" . $token));
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
        $result = json_decode(ssl::get_content(self::$serverurl . "api/team1/otl/" . $token));
        return $result;
    }
}
?>