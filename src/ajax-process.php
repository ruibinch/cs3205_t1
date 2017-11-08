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

    // Misc
    if (isset($_POST['attachRecords'])) {
        echo displayAttachedRecords($_POST['attachRecords']);
    }

    // Document Sharing
    if (isset($_POST['therapistArray'])) {
        echo shareDocumentsWithTherapists($_POST['therapistArray']);
    }

    // ===============================================================================
    //                       CHALLENGE-RESPONSE AUTHENTICATION
    // ===============================================================================
            
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
            $fake_salt = substr(password_hash(strval(mt_rand()), PASSWORD_DEFAULT), 0, 29); // to prevent timing attacks
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
    //                          TREATMENT RELATIONS
    // ===============================================================================
    
    function createTreatmentReq($patientId, $therapistId, $consentSettings) {
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/create/' 
                                . $patientId . '/' . $therapistId . '/' . $consentSettings[0] . '/' . $consentSettings[1]));
        return $response->result;
    }

    //TODO: add CSRF validation
    function acceptTreatmentReq($treatmentId) {
        createConsentPermissions($treatmentId);
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/update/' . $treatmentId));
        return $response->result;
    }

    function rejectTreatmentReq($treatmentId) {
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
    }

    function removeTreatmentReq($treatmentId) {
        removeAdditionalElements($treatmentId);
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/delete/' . $treatmentId));
        return $response->result;
    }

    // ===============================================================================
    //                             CONSENT RELATIONS
    // ===============================================================================
    
    // Create consent permissions between the newly assigned therapist and the records of the patient; all defaulted to false
    function createConsentPermissions($treatmentId) {
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/' . $treatmentId));
        $patientId = $response->patientId;
        $therapistId = $response->therapistId;
        $currentConsent = $response->currentConsent; 

        $patient_records_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/all/' . $patientId));
        if (isset($patient_records_json->records)) {
            $patient_records = $patient_records_json->records;
            for ($i = 0; $i < count($patient_records); $i++) {
                $record = $patient_records[$i];
                // caters for the case where the patient is also a therapist - excludes the documents owned by the patient as a therapist
                if ($record->type === "File") {
                    if ($record->subtype != "document") {
                        $create_consent_response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/create/' . $therapistId . '/' . $record->rid));
                    }
                } else {
                    $create_consent_response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/create/' . $therapistId . '/' . $record->rid));
                }
            }
        }
        
        if ($currentConsent) {
            setAllConsentsToTrue($treatmentId);
        }
    }

    function removeAdditionalElements($treatmentId) {
        $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/' . $treatmentId));
        $patientId = $response->patientId;
        $therapistId = $response->therapistId;

        removeDocumentsAndAssociatedConsents($patientId, $therapistId);
        removeOtherConsents($patientId, $therapistId);
    }

    // Removes all documents associated to the patient that had been written by the therapist
    function removeDocumentsAndAssociatedConsents($patientId, $therapistId) {
        $patient_consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/' . $patientId));
        if (isset($patient_consents_json->consents)) {
            $patient_consents = $patient_consents_json->consents;
            for ($i = 0; $i < count($patient_consents); $i++) {
                $record = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/get/' . $patient_consents[$i]->rid));
                if ($record->therapistId === $therapistId) {
                    $delete_document_response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/delete/' . $record->rid . '/' . $therapistId));
                    $delete_consent_response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/delete/' . $patient_consents[$i]->consentId));
                }
            }
        }
    }

    // Removes all associated consents between a therapist and a patient's records when the patient removes an assigned therapist
    function removeOtherConsents($patientId, $therapistId) {
        // Get list of record IDs of the patient records
        $patient_records_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/all/' . $patientId));
        $patient_records_ids = array();
        if (isset($patient_records_json->records)) {
            $patient_records = $patient_records_json->records;
            for ($i = 0; $i < count($patient_records); $i++) {
                array_push($patient_records_ids, $patient_records[$i]->rid);
            }
        }

        // Iterate through the list of consents associated with the therapist and delete the consents that are for records of the deleted patient
        $therapist_consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/user/' . $therapistId));
        if (isset($therapist_consents_json->consents)) {
            $therapist_consents = $therapist_consents_json->consents;
            for ($i = 0; $i < count($therapist_consents); $i++) {
                if (in_array($therapist_consents[$i]->rid, $patient_records_ids)) {
                    $delete_consent_response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/delete/' . $therapist_consents[$i]->consentId));
                }
            }
        }

    }

    // Iterates through the boolean array
    // If an element is set to true, then toggle the status value of the consent with the ID corresponding to the array index value
    function updateConsentStatus($consentChanges) {
        for ($i = 0; $i < count($consentChanges); $i++) {
            if ($consentChanges[$i]) { // if the value has been toggled
                $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/update/' . $i));
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
        return ssl::post_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/update/consentsetting', $consent_settings_json, array('Content-Type: application/json'));
    }

    // Sets all the consents between the patient and the therapist to true
    function setAllConsentsToTrue($treatmentId) {
        $treatment = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/treatment/' . $treatmentId));
        $consents_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/owner/' . $treatment->patientId . '/' . $treatment->therapistId));
        $consents = $consents_json->consents;
        for ($i = 0; $i < count($consents); $i++) {
            $consent = $consents[$i];
            if (!$consent->status) {
                $response = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/consent/update/' . $consent->consentId));
            }
        }
    }

    // ===============================================================================
    //                                  MISC
    // ===============================================================================
    
    function displayAttachedRecords($attached_records_list) {
        
        /*
        $var = "";
        for ($i = 0; $i < count($attached_records_list); $i++) {
            $var .= gettype($attached_records_list[$i]) . ", "; 
        }
        return $var;
        */
        
        $count = 1;
        $line = "<br>";
        for ($i = 0; $i < count($attached_records_list); $i++) {
            if ($attached_records_list[$i] === "true") {
                $line .= "<span>";
                $line .= $count . ". <a href='#'><u>"; // TODO - include link to view the file
                $line .= json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/record/' . $i))->title;
                $line .= "</u></a></span>";
                $line .= "<br>";
                $count++;
            }
        }

        return $line;
    }

    function shareDocumentsWithTherapists($therapist_array) {
        $t_array = array();
        foreach($therapist_array AS $therapist) {
            $therapist = json_decode($therapist);
            $consent_string = getConsentJson($therapist->therapist, $therapist->rid, $therapist->owner);
            if ($therapist->isChecked) {
                if (strcmp($consent_string, "-1") == 0) { // Consent doesn't exist
                    $result = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/create/".$therapist->therapist."/".$therapist->rid));
                    $consent_json = json_decode(getConsentJson($therapist->therapist, $therapist->rid, $therapist->owner));
                    $result = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/update/".$consent_json->consentId));
                    array_push($t_array, $therapist->therapist." consent created");
                } else {
                    $consent_json = json_decode($consent_string);
                    if (!$consent_json->status) {
                        $result = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/update/".$consent_json->consentId));
                        array_push($t_array, $therapist->therapist." consent toggled to true");
                    }
                }
            } else {
                if (strcmp($consent_string, "-1") !== 0) { // If consent exist
                    $consent_json = json_decode($consent_string);
                    if ($consent_json->status) {
                        $result = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/update/".$consent_json->consentId));
                        array_push($t_array, $therapist->therapist." consent toggled to false");
                    }
                }
            }
        }
        return json_encode($t_array);
    }

    function getConsentJson($uid, $rid, $tid) {
        $consentId = "-1"; // Consent doesn't exist
        $consent_array = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/owner/".$tid."/".$uid));
        if (!isset($consent_array->result)) {
            $consent_array = $consent_array->consents;
            foreach ($consent_array AS $consent_elem) {
                if (strcmp($consent_elem->rid, $rid) == 0) {
                    $consentId = ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4']."api/team1/consent/".$consent_elem->consentId);
                }
            }
        }
        return $consentId;
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
