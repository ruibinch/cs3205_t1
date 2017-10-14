<?php

    session_start();

    // Challenge-response authentication process
    if (isset($_POST['input_username'])) {
        echo getChallengeAndSalt($_POST['input_username']);
    } else if (isset($_POST['response'])) {
        echo verifyResponse($_POST['response']);
    }

    // Treatment relations
    if (isset($_POST['patientId']) && isset($_POST['therapistId'])) {
        echo createTreatmentReq($_POST['patientId'], $_POST['therapistId']);
    } else if (isset($_POST['acceptTreatmentId'])) {
        echo acceptTreatmentReq($_POST['acceptTreatmentId']);
    } else if (isset($_POST['rejectTreatmentId'])) {
        echo removeTreatmentReq($_POST['rejectTreatmentId']);
    } else if (isset($_POST['removeTreatmentId'])) {
        echo removeTreatmentReq($_POST['removeTreatmentId']);
    }

    // ===============================================================================
    //                       CHALLENGE-RESPONSE AUTHENTICATION
    // ===============================================================================
            
    function getChallengeAndSalt($input_username) {
        $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/username/' . $input_username));
        $_SESSION['user_json'] = $user_json;
        $challenge = mt_rand(); // random number
        $_SESSION['challenge'] = $challenge;
        if (isset($user_json->salt)) {
            $response = array('challenge' => $challenge, 'salt' => $user_json->salt);
        } else { // user does not exist
            $fake_salt = substr(password_hash(strval(mt_rand()), PASSWORD_DEFAULT), 0, 29);
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
                return 1;
            }
        }
        return 0;
    }

    // ===============================================================================
    //                          TREATMENT RELATIONS
    // ===============================================================================
    
    function createTreatmentReq($patientId, $therapistId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/create/' . $patientId . '/' . $therapistId));
        return $response->result;
    }

    //TODO: add CSRF validation
    function acceptTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/update/' . $treatmentId));
        return $response->result;
    }

    function removeTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
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

?>