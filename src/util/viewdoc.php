<?php


$XSS = 1;//switch for xss protection
$FILEINC = 1;//switch for file inclusion protection
$CSRF = 1;//switch for CSRF protection

$uid;
checkJWT($_COOKIE[token]);//

$fileInfo = query($_GET["file"]);//TODO: to be implemented
$fileTitle = $fileInfo[0];
$fileURL = $fileInfo[1];
$fileOwner = $fileInfo[2];
$fileID = $fileInfo[3];


$checkConsent = checkConsent($fileID, $_COOKIE);

if (mysql_num_rows($checkConsent) == 0) {
    header('HTTP/1.0 404 File Not Found');
    die('File not found on the server');
} else {
    $checkConsent = query("SELECT * FROM consent WHERE uid = ..."); // TODO: change after rest api is finished
    if (mysql_num_rows($checkConsent) == 0) {
        header('HTTP/1.0 403 Forbidden');
        die('You are not allowed to access this file.');
    } else {
        
    }
}

function checkJWT($token, $key) {
    $decoded = JWT::decode( $token, base64_decode(strtr($key, '-_', '+/')) );
}
?>