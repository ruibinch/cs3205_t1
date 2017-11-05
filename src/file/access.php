<?php 
include_once '../util/jwt.php';
include_once '../util/otl.php';
include_once '../util/ssl.php';
$result = WebToken::verifyToken($_COOKIE["jwt"]);
$uid = $result->uid;
$user_json = json_decode(ssl::get_content('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/uid/' . $uid));
$user_type = $result->istherapist ? "therapist" : "patient";

if (!isset($_GET['otl'])) {
    header('400 Bad Request.');
    die();
} else {
    $otl = OneTimeToken::getToken($_GET['otl']);
    if (!isset($otl->filepath)) {
        echo 'here';
        header('400 Bad Request.');
        die();
    }
    $path = $_SERVER['DOCUMENT_ROOT']."/tmp/".$otl->filepath;
    if (!file_exists($path)) {
        header('404 Not Found.');
        die();
    }
    header('Content-Disposition: attachment; filename='.basename($path));
    $file = file_get_contents($path);
    echo $file;
    OneTimeToken::deleteToken($_GET['otl']);
    unlink($path);
}
?>