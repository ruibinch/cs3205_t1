<?php

class OneTimeToken
{

    private static $serverurl = "http://172.25.76.76/";

    /*
     * @param $uid
     * @param $filePath - relative path of the file
     * @param $CSRFToken
     */
    static function generateToken($uid, $filePath, $CSRFToken)
    {
        $string = bin2hex(random_bytes(20));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::$serverurl . "api/team1/otl/create/" . $string . "/" . $uid . "/" . $filePath . "/" . $CSRFToken);
        curl_setopt($curl, CURLOPT_PORT, 80);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl));
        if ($result->result == 0)
            throw new Exception('Fail to generate token');
        return $string;
    }

    /*
     * @param $token
     * @return true if the token is found and deleted, false if it fails
     */
    static function deleteToken($token)
    {
        $result = self::getToken($token);
        if (isset($result->result) && ! ($result->result))
            return false;
        else {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, self::$serverurl . "api/team1/otl/delete/" . $token);
            curl_setopt($curl, CURLOPT_PORT, 80);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = json_decode(curl_exec($curl));
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
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::$serverurl . "api/team1/otl/" . $token);
        curl_setopt($curl, CURLOPT_PORT, 80);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl));
        return $result;
    }
}
?>