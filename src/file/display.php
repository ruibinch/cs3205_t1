<?php 
include_once '../util/jwt.php';
include_once '../util/otl.php';
include_once '../util/csrf.php';
$result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
$uid = $result->uid;
$user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $uid));
$user_type = $result->istherapist ? "therapist" : "patient";

if (!isset($_GET['rid']) || !isset($_GET['csrf'])) {
    header('400 Bad Request.');
    die();
} else {
    $rid = $_GET['rid'];
    function get_rid($e) {
        return $e->rid;
    }
    if (false) {
        //TODO
        header('400 Bad Request.');
        die();
    }  else {
        $csrf = json_decode(ssl::get_content("https://172.25.76.76/api/team1/csrf/".$_GET['csrf']));
        if (isset($csrf->result) || $csrf->expiry > time() || $csrf->description != "viewdoc") {
            header('400 Bad Request.');
            die();
        }
        CSRFToken::deleteToken($_GET['csrf']);
        $csrftoken = CSRFToken::generateToken($uid, "viewdoc");
        setcookie("vcsrf", $csrftoken, time()+3600);
        $filecontent = ssl::get_content("https://172.25.76.76/api/team1/record/get/".$rid);
        $details = json_decode(ssl::get_content("https://172.25.76.76/api/team1/record/".$rid));
        $filepath = $_SERVER['DOCUMENT_ROOT']."/tmp/".hash("md5", $uid)."/".hash("md5", $filecontent)."/".basename($details->absolutePath);
        $file = fopen($filepath, "w");
        fwrite($file, $filecontent);
        $otl = OneTimeToken::generateToken($uid, hash("md5", $uid)."/".hash("md5", $filecontent)."/".basename($details->absolutePath), $_GET['csrf']);
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
