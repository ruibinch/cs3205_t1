<?php

    include_once 'util/ssl.php';

    // Challenge-response authentication process
    if (isset($_POST['inputUsername']) & isset($_POST['loginSystem']) && isset($_POST['userType'])) {
        session_start();
        echo getChallengeAndSalt($_POST['inputUsername'], $_POST['loginSystem'], $_POST['userType']);
    } else if (isset($_POST['challengeResponse'])) {
        session_start();
        echo verifyResponse($_POST['challengeResponse']);
    }

    function getChallengeAndSalt($input_username, $login_system, $user_type) {
        $_SESSION['login_system'] = $login_system;
        $_SESSION['user_type'] = $user_type;

        if ($login_system === "hcsystem") {
            $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/' . $input_username));
        } else if ($login_system === "mgmtconsole") {
            $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/admin/' . $input_username));
        }
        $_SESSION['user_json'] = $user_json;
        $challenge = mt_rand(); // random number
        $_SESSION['challenge'] = $challenge;
        if (isset($user_json->salt)) {
            $response = array('challenge' => $challenge, 'salt' => $user_json->salt);
        } else { // user does not exist
            $secret = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['secret'];
            $options = [ 'salt' => $secret ];
            $fake_salt = substr(password_hash($input_username, PASSWORD_DEFAULT), 0, 29); // to prevent timing attacks
            $response = array('challenge' => $challenge, 'salt' => $fake_salt);
        }

        return json_encode($response);
    }

    function verifyResponse($challenge_response) {
        $user_json = $_SESSION['user_json'];
        if (isset($user_json->password)) {
            $user_pwhash = $user_json->password;
            
            $sha_pw_with_challenge = hash("sha256", $user_pwhash . $_SESSION['challenge']); // H(hash,challenge)
            $sha_pw_with_challenge_binary = hexToBinary($sha_pw_with_challenge); // H(hash, challenge), in binary
            $check = doXOR($sha_pw_with_challenge_binary, $challenge_response); // H(hash,challenge) XOR response, in binary
            $check_char = binaryToChar($check); // binary to ASCII char
            $sha_check_char = hash("sha256", $check_char); // H(H(hash,challenge) XOR response)
            
            if (strcmp($sha_check_char, $user_pwhash) === 0) {
                return processLogin();
            } else {
                return "login.php";
            }
        } else {
            return "login.php";
        }
    }

    function processLogin() {
        
        if ($_SESSION['login_system'] === "hcsystem") {
            include_once 'util/jwt.php';
            $user_json = $_SESSION['user_json'];
            $user_type = $_SESSION['user_type'];

            if ($user_type === "therapist" && $user_json->qualify !== 1) {
                return "login.php";
                //header("location: ../login.php?err=1");
                //die();
            }
            
            //TODO: change the dummy key here to the real key; change $secure to true
            setcookie("jwt", WebToken::getToken($user_json->uid, $user_type === "therapist"), 
                    time()+3600, "/", null, true, true);
            return "main.php";
            //exit();

        } else if ($_SESSION['login_system'] === "mgmtconsole") {
            include_once 'util/jwt-admin.php';
            
            $decode = $_SESSION['user_json'];
            if (isset($decode->username)) {
                //TODO: change the dummy key here to the real key
                setcookie("jwt", WebToken::getToken($decode->admin_id),time()+3600, "/", null, true, true);
                $_SESSION['loggedin'] = $decode->username;
                return "management/console.php";
                //header("location: management/console.php");
                //exit();
            } else {
                $_SESSION = array();
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000,
                        $params["path"], $params["domain"],
                        $params["secure"], $params["httponly"]
                );
                }
                session_destroy();
                return "login.php?to=console";
                //header("Location: login.php?to=console&err=1");
                //exit();
            }
            
        }
    }

?>