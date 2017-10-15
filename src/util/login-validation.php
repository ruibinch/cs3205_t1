<?php

    include_once 'jwt.php';

    // dummy data
    // TODO: store the jwt secret key to some place safe
    $dummy_key = "dummykey";

    $username = $_POST['username'];
    $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/username/' . $username));

    if ($_POST['user_type'] === "therapist" && $user_json->qualify !== 1) {
        header("location: ../login.php?err=1");
        die();
    }

    //TODO: change the dummy key here to the real key; change $secure to true
    setcookie("jwt", WebToken::getToken($user_json->uid, $_POST['user_type'] === "therapist", $dummy_key), 
            time()+3600, "/", null, true, true);
    header("location: ../main.php");
    exit();

?>
