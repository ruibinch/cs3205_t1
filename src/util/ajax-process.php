<?php

    // Challenge-response authentication process
    if (isset($_POST['input_username'])) {
        session_start();
        echo getChallengeAndSalt($_POST['input_username']);
    } else if (isset($_POST['response'])) {
        echo verifyResponse($_POST['response']);
    }

    // Treatment relations
    if (isset($_POST['patientId']) && isset($_POST['therapistId']) && isset($_POST['consentSettings'])) {
        echo createTreatmentReq($_POST['patientId'], $_POST['therapistId'], $_POST['consentSettings']);
    } else if (isset($_POST['acceptTreatmentId'])) {
        echo acceptTreatmentReq($_POST['acceptTreatmentId']);
    } else if (isset($_POST['rejectTreatmentId'])) {
        echo rejectTreatmentReq($_POST['rejectTreatmentId']);
    } else if (isset($_POST['removeTreatmentId'])) {
        echo removeTreatmentReq($_POST['removeTreatmentId']);
    }

    // Consent relations
    if (isset($_POST['consentChanges'])) {
        updateConsentStatus($_POST['consentChanges']);
    }
    if (isset($_POST['treatmentId']) && isset($_POST['currentConsentSetting']) && isset($_POST['futureConsentSetting'])) {
        echo updateDefaultConsentSettings($_POST['treatmentId'], $_POST['currentConsentSetting'], $_POST['futureConsentSetting']);
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
    
    function createTreatmentReq($patientId, $therapistId, $consentSettings) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/create/' 
                                . $patientId . '/' . $therapistId . '/' . $consentSettings[0] . '/' . $consentSettings[1]));
        return $response->result;
    }

    //TODO: add CSRF validation
    function acceptTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/update/' . $treatmentId));
        createConsentPermissions($treatmentId);
        return $response->result;
    }

    function rejectTreatmentReq($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
    }

    function removeTreatmentReq($treatmentId) {
        removeConsentPermissions($treatmentId);
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
    }

    // ===============================================================================
    //                             CONSENT RELATIONS
    // ===============================================================================
    
    // Create consent permissions between the newly assigned therapist and the records of the patient; all defaulted to false
    function createConsentPermissions($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/' . $treatmentId));
        $patientId = $response->patientId;
        $therapistId = $response->therapistId;
        $currentConsent = $response->currentConsent; 

        $patient_records_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/' . $patientId));
        if (isset($patient_records_json->records)) {
            $patient_records = $patient_records_json->records;
            for ($i = 0; $i < count($patient_records); $i++) {
                $create_consent_response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/create/' . $therapistId . '/' . $patient_records[$i]->rid));
            }
        }
        
        if ($currentConsent) {
            setAllConsentsToTrue($treatmentId);
        }
    }

    // Removes all associated consents between a therapist and a patient's records when the patient removes an assigned therapist
    function removeConsentPermissions($treatmentId) {
        $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/' . $treatmentId));
        $patientId = $response->patientId;
        $therapistId = $response->therapistId;

        // Get list of record IDs of the patient records
        $patient_records_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/record/all/' . $patientId));
        $patient_records_ids = array();
        if (isset($patient_records_json->records)) {
            $patient_records = $patient_records_json->records;
            for ($i = 0; $i < count($patient_records); $i++) {
                array_push($patient_records_ids, $patient_records[$i]->rid);
            }
        }

        // Iterate through the list of consents associated with the therapist and delete the consents that are for records of the deleted patient
        $therapist_consents_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/user/' . $therapistId));
        if (isset($therapist_consents_json->consents)) {
            $therapist_consents = $therapist_consents_json->consents;
            for ($i = 0; $i < count($therapist_consents); $i++) {
                if (in_array($therapist_consents[$i]->rid, $patient_records_ids)) {
                    $delete_consent_response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/delete/' . $therapist_consents[$i]->consentId));
                }
            }

        }
    }

    // Iterates through the boolean array
    // If an element is set to true, then toggle the status value of the consent with the ID corresponding to the array index value
    function updateConsentStatus($consentChanges) {
        for ($i = 0; $i < count($consentChanges); $i++) {
            if ($consentChanges[$i]) { // if the value has been toggled
                $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/update/' . $i));
            }
        }
    }

    // Updates the current and future consent status flags
    function updateDefaultConsentSettings($treatmentId, $currentConsent, $futureConsent) {
        if ($currentConsent === "true") {
            setAllConsentsToTrue($treatmentId);
        }

        $consent_settings = array('id' => $treatmentId, 'currentConsent' => $currentConsent, 'futureConsent' => $futureConsent);
        $consent_settings_json = json_encode($consent_settings);
        $ch = curl_init('http://172.25.76.76/api/team1/treatment/update/consentsetting');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $consent_settings_json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        return curl_exec($ch);
    }

    // Sets all the consents between the patient and the therapist to true
    function setAllConsentsToTrue($treatmentId) {
        $treatment = json_decode(file_get_contents('http://172.25.76.76/api/team1/treatment/' . $treatmentId));
        $consents_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/owner/' . $treatment->patientId . '/' . $treatment->therapistId));
        $consents = $consents_json->consents;
        for ($i = 0; $i < count($consents); $i++) {
            $consent = $consents[$i];
            if (!$consent->status) {
                $response = json_decode(file_get_contents('http://172.25.76.76/api/team1/consent/update/' . $consent->consentId));
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

?>