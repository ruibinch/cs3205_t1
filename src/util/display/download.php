<?php
$dir = "/tmp/";

if (! isset($_GET["ott"])) {
    header('HTTP/1.1 403 Forbidden');
    echo 'You are forbidden!';
    die();
} else {
    $dir = __DIR__ . "/../../tmp/";
    $ott = $_GET["ott"];
    $result = json_decode(file_get_contents('http://cs3205-4-i.comp.nus.edu.sg/api/team1/otl/' . $ott));
    if (! isset($result->result)) {
        $filepath = $dir . $result->filepath;
        if (file_exists($filepath)) {
            $strfile = file_get_contents($filepath);
            header("Content-Type: application/force-download");
            header("Content-Disposition: attachment; filename=\"".str_replace(" ", "_", $result->filepath)."\"");
            echo $strfile;
        } else {
            header('HTTP/1.1 403 Forbidden');
            echo 'You are forbidden!';
            die();
        }
    } else {
        header('HTTP/1.1 403 Forbidden');
        echo 'You are forbidden!';
        die();
    }
}

?>