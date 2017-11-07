<?php 
include_once '../util/jwt.php';
include_once '../util/otl.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"]);
$uid = $result->uid;
$user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $uid));
$user_type = $result->istherapist ? "therapist" : "patient";

if (!isset($_GET['otl'])) {
    header('HTTP/1.0 400 Bad Request.');
    die();
} else {
    $otl = OneTimeToken::getToken($_GET['otl']);
    if (!isset($otl->filepath)) {
        Log::recordTX($uid, "Warning", "Unrecognised otl: ".json_encode($otl));
        header('HTTP/1.0 400 Bad Request.');
        die();
    }
    $path = $_SERVER['DOCUMENT_ROOT']."/tmp/".$otl->filepath;
    if (!file_exists($path)) {
        Log::recordTX($uid, "Error", "File not found on server: ".$path);
        header('HTTP/1.0 404 Not Found.');
        die();
    } else if (!preg_match("#^".$_SERVER['DOCUMENT_ROOT']."/tmp/#", realpath($path))) {
        Log::recordTX($uid, "Error", "LFI path detected: ".$path);
        header('HTTP/1.0 400 Bad Request.');
        die();
    }
    if ($otl->datatype == "video")
        header('Content-Type: video/mp4');
    header('Content-Disposition: attachment; filename='.basename($path));
    $file = file_get_contents($path);
    Log::recordTX($uid, "Info", "Accessed file: ".$path);
    echo $file;
    OneTimeToken::deleteToken($_GET['otl']);
    unlink($path);
    Log::recordTX($uid, "Info", "Delete File on server: ".$path);
}
?>
