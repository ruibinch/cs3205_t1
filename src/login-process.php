<?php

    include_once 'util/ssl.php';
    session_start();

    // Challenge-response authentication process
    if (isset($_POST['inputUsername']) & isset($_POST['loginSystem']) && isset($_POST['userType'])) {
        echo getChallengeAndSalt($_POST['inputUsername'], $_POST['loginSystem'], $_POST['userType']);
    } else if (isset($_POST['challengeResponse'])) {
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
            $hex_digest = hash("sha256", $input_username . $secret);
            $bin_digest = hexToBinary($hex_digest);
            $fake_salt = "$2y$10$" . toBase64($bin_digest);
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

    // ===============================================================================
    //                              HELPER FUNCTIONS
    // ===============================================================================
      
    // XORs the 2 strings after left-padding with zeroes to an equal length
    function doXOR($a, $b) {
        // pad both to equal length
        if (strlen($a) > strlen($b)) {
            while (strlen($a) > strlen($b)) {
                $b = '0' . $b;
            }
        } else if (strlen($b) > strlen($a)) {
            while (strlen($b) > strlen($a)) {
                $a = '0' . $a;
            }
        }

        $result = "";
        for ($i = 0; $i < strlen($a); $i++) {
            if (strcmp($a[$i], $b[$i]) === 0) { // equal
                $result = $result . "0";
            } else {
                $result = $result . "1";
            }
        }
        return $result;
    }

    // Converts a string of hex characters to its binary representation
    function hexToBinary($input) {
        $input_binary = "";
        for ($i = 0; $i < strlen($input); $i++) {
            $input_binary = $input_binary . getBinValue(substr($input, $i, 1));
        }
        return $input_binary;
    }

    function getBinValue($num) {
        switch ($num) {
            case "0":
                return "0000";
            case "1":
                return "0001";
            case "2":
                return "0010";
            case "3":
                return "0011";
            case "4":
                return "0100";
            case "5":
                return "0101";
            case "6":
                return "0110";
            case "7":
                return "0111";
            case "8":
                return "1000";
            case "9":
                return "1001";
            case "a": case "A":
                return "1010";
            case "b": case "B":
                return "1011";
            case "c": case "C":
                return "1100";
            case "d": case "D":
                return "1101";
            case "e": case "E":
                return "1110";
            case "f": case "F":
                return "1111";
        }
    }

    // For each block of 8-bits, convert it to its decimal value and then obtain the corresponding ASCII character
    function binaryToChar($input) {
        $input_hex = "";
        for ($i = 0; $i < strlen($input); $i+=8) {
            $value_dec = base_convert(substr($input, $i, 8), 2, 10);
            $input_hex = $input_hex . chr($value_dec);
        }
        return $input_hex;
    }

    // reads in a 256-bit string and returns 22 base64-chars
    function toBase64($bin_string) {
        $output = "";
        $index_table = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
                            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
                            "0", "1", "2", "3", "4", "5", "6", "7", "8", "9", ".", "/");

        for ($i = 0; $i < 22; $i++) {
            $bin_value = base_convert(substr($bin_string, $i*6, 6), 2, 10);
            $output .= $index_table[$bin_value];
        }
        
        return $output;
    }

?>