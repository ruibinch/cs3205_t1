<?php
// Login validation for healthcare system
include_once 'jwt.php';
// session_start();

// dummy data
// TODO: strore the jwt secret key to some place safe
$dummy_username = "team1";
$dummy_password = '$2y$10$NHpcPdotapEtq5t2HR.iV.OVHzV8oCymH9n3Fwhnh0CYP9xGE6oG6'; // plaintext: corgi
$dummy_key = "dummykey";

$input_username = $_POST['hc-username'];
$input_password = $_POST['hc-password'];
$user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/username/' . $input_username));

if (isset($user_json->password)) {
    if (password_verify($input_password, $user_json->password)) {
        /*
         * $_SESSION['user_json'] = $user_json;
         * if ($_POST['user_type'] === "therapist") {
         * if ($user_json->qualify === 1) { // check that a user qualifies to be a therapist
         * $_SESSION['user_type'] = "therapist";
         * header("location: ../main.php");
         * } else {
         * session_destroy();
         * header("location: ../login.php?err=1");
         * }
         * } else {
         * $_SESSION['user_type'] = $_POST['user_type'];
         * header("location: ../main.php");
         * exit();
         * }
         */
        if ($_POST['user_type'] === "therapist" && $user_json->qualify !== 1) {
            header("location: ../login.php?err=1");
            die();
        }
        //TODO: change the dummy key here to the real key; change $secure to true
        setcookie("jwt", WebToken::getToken($user_json->uid, $_POST['user_type'] === "therapist", $dummy_key), 
            time()+3600, "/", null, true, true);
        header("location: ../main.php");
        exit();
    } else {
        header("location: ../login.php?err=1");
        die();
    }
} else if ($_POST['hc-username'] === $dummy_username && password_verify($_POST['hc-password'], $dummy_password)) {
    // FOR EASE OF TESTING, TO BE REMOVED
    setcookie("jwt", WebToken::getToken(-1, $_POST['user_type'] === "therapist", $dummy_key),
        "/", "", true, true);
    header("location: ../main.php");
    exit();
} else {
    session_destroy();
    header("location: ../login.php?err=2");
    die();
}

?>
