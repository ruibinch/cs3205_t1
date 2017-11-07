<?php 
include_once '../util/jwt.php';
include_once '../util/otl.php';
include_once '../util/csrf.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"]);
$user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";
$uid = $result->uid;

if (!isset($_GET['rid']) || !isset($_GET['csrf'])) {
    //Bad GET url
    header('HTTP/1.0 400 Bad Request.');
    die();
} else {
    $rid = $_GET['rid'];
    if (!json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/check/".$uid."/".$rid))->result) {
        //No access to this file
        Log::recordTX($uid, "Warning", "Trying to access unprivilaged file: ".$rid);
        header('HTTP/1.0 400 Bad Request.');
        die();
    } else {
        $csrf = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/csrf/".$_GET['csrf']));
        if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "viewdoc" || $csrf->uid != $uid) {
            //invalid csrf token
            Log::recordTX($uid, "Warning", "Invalid csrf when accessing display.php");
            header('HTTP/1.0 400 Bad Request.');
            die();
        }
        //refresh csrf token
        CSRFToken::deleteToken($_GET['csrf']);
        $csrftoken = CSRFToken::generateToken($uid, "viewdoc");
        setcookie("vcsrf", $csrftoken, time()+3600);
        
        //download file
        $filecontent = ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/record/get/".$rid);
        $details = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/record/".$rid));
        if (preg_match("#^.ht#", basename($details->absolutePath))) {
            //prevent creation of conf files like .htaccess
            Log::recordTX($uid, "Error", ".ht* file writing detected: " . $details->absolutePath);
            header('HTTP/1.0 400 Bad Request.');
            die();
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)))
            mkdir($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid), 0711);
        if (!file_exists($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)))
            mkdir($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent), 0711);
        if (isset($details->absolutePath)) {
            $filepath = $_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)."/".urlencode(basename($details->absolutePath));
            //generate one time link
            $otl = OneTimeToken::generateToken($uid, hash("md5", $uid)."/".hash("md5", $filecontent)."/".urlencode(basename($details->absolutePath)), $_GET['csrf'], isset($details->subtype) ? $details->subtype : "file");
        } else {
            $filepath = $_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)."/file.json";
            //generate one time link
            $otl = OneTimeToken::generateToken($uid, hash("md5", $uid)."/".hash("md5", $filecontent)."/file.json", $_GET['csrf'], "file");
        }
        $file = fopen($filepath, "w");
        fwrite($file, $filecontent);
        Log::recordTX($uid, "Info", "Store file on server: ".$filepath);
        $fileurl = "/file/access.php?otl=".$otl;
        if (isset($_GET['method']) && $_GET['method'] == download) {
            header('Location: '.$fileurl);
            die();
        }
        
        //generate html code for diff file types
        if ($details->type == "Heart Rate") {
            include '../util/display/heartrate.php';
        } else if ($details->type == "Time Series") {
            include '../util/display/timeseries.php';
        } else if ($details->subtype == "image") {
            include '../util/display/image.php';
        } else if ($details->subtype == "video") {
            include '../util/display/video.php';
        } else if ($details->subtype == "document") {
            include '../util/display/document.php';
        }
    }
}
?>
