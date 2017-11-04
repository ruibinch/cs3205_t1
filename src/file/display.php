<?php 
include_once '../util/jwt.php';
include_once '../util/otl.php';
include_once '../util/csrf.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
$user_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/' . $result->uid));
$user_type = $result->istherapist ? "therapist" : "patient";
$uid = $result->uid;

if (!isset($_GET['rid']) || !isset($_GET['csrf'])) {
    header('HTTP/1.0 400 Bad Request.');
    die();
} else {
    $rid = $_GET['rid'];
    if (!json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/consent/check/".$uid."/".$rid))->result) {
        header('HTTP/1.0 400 Bad Request.');
        die();
    } else {
        $csrf = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/csrf/".$_GET['csrf']));
        if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "viewdoc") {
            header('HTTP/1.0 400 Bad Request.');
            die();
        }
        CSRFToken::deleteToken($_GET['csrf']);
        $csrftoken = CSRFToken::generateToken($uid, "viewdoc");
        setcookie("vcsrf", $csrftoken, time()+3600);
        $filecontent = ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/get/".$rid);
        $details = json_decode(ssl::get_content("http://cs3205-4-i.comp.nus.edu.sg/api/team1/record/".$rid));
        if (!file_exists($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)))
            mkdir($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid), 0711);
        if (!file_exists($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)))
            mkdir($_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent), 0711);
        $filepath = $_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)."/".basename($details->absolutePath);
        $file = fopen($filepath, "w");
        fwrite($file, $filecontent);
        $otl = OneTimeToken::generateToken($uid, hash("md5", $uid)."/".hash("md5", $filecontent)."/".basename($details->absolutePath), $_GET['csrf'], "file");
        $fileurl = "/file/access.php?otl=".$otl;
        if ($details->type == "Time Series") {
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
