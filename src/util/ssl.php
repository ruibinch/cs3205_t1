<?php

/*
 * Usage:
 * include_once 'ssl.php';
 * $result = ssl::get_content($url);
 */
class ssl
{
    private static $certFile = "/usr/keys/team1-cert.pem";

    private $keyFile = "/usr/keys/team1-key.pem";

    private $caInfo = "/usr/keys/cacert.crt";

    /*
     * Use this method to set necessary SSL credentials for cURL reference passed into this method.
     *
     * @param $curl the curl variable for calling api
     * @return $curl
     */
    static function setSSL($curl)
    {
        global $certFile, $keyFile, $caInfo;
        // curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        // Set ssl key here
        curl_setopt($curl, CURLOPT_SSLKEY, $keyFile);
        // The --cert option
        curl_setopt($curl, CURLOPT_SSLCERT, $certFile);
        curl_setopt($curl, CURLOPT_CAINFO, $caInfo);
        // curl_setopt($curl, CURLOPT_CAPATH, '/home/sadm/keys/ssl/');
        return $curl;
    }
    
    static function get_content($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        self::setSSL($curl);

        return curl_exec($curl);
    }
}
?>
	