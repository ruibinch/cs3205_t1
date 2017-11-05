<?php

    include_once 'util/ssl.php';
    include_once 'util/jwt.php';
    include_once 'util/logger.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"]);

    $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";

    $num_phone = count($user_json->phone);
    $num_address = count($user_json->address);
    $settings_save = false;
    $hasError = false;
    $error_arr = array();
    $post_result = "";
    
    // Input
    $username = $fname = $lname = $dob = $nationality = $ethnicity = $drugAllergy = $phone0 = $phone1 = $phone2 = $addr0 = $addr1 = $addr2 = $zip0 = $zip1 = $zip2 = "";
    $drugAllergy = false;

    // Error messages
    $userErr = $fnameErr = $lnameErr = $dobErr = $nationalityErr = $ethnicityErr = $phone0Err = $phone1Err = $phone2Err = $addr0Err = $addr1Err = $addr2Err = $zip0Err = $zip1Err = $zip2Err = "";

    function sanitise($input) {
      $input = trim($input);
      $input = stripcslashes($input);
      $input = htmlspecialchars($input);
      return $input;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = sanitise($_POST["input-username"]);
        $fname = sanitise($_POST["input-firstname"]);
        $lname = sanitise($_POST["input-lastname"]);
        $dob = sanitise($_POST["input-dob"]);
        $nationality = sanitise($_POST["input-nationality"]);
        $ethnicity = sanitise($_POST["input-ethnicity"]);
        $drugAllergy = sanitise($_POST["input-drugAllergy"]);
        $phone0 = sanitise($_POST["input-phone0"]);
        $phone1 = sanitise($_POST["input-phone1"]);
        $phone2 = sanitise($_POST["input-phone2"]);
        $addr0 = sanitise($_POST["input-address0"]);
        $addr1 = sanitise($_POST["input-address1"]);
        $addr2 = sanitise($_POST["input-address2"]);
        $zip0 = sanitise($_POST["input-zipcode0"]);
        $zip1 = sanitise($_POST["input-zipcode1"]);
        $zip2 = sanitise($_POST["input-zipcode2"]);

        // Check username
        if (empty($username)) { // Check empty field
          $hasError = true;
          $userErr = "Input required";
        } else if (preg_match("/[^A-Za-z0-9]/", $username)) { // Check username format
          $hasError = true;
          $userErr = "Invalid username format, please only use alphanumeric characters";
        } else { // Check availability
          $username_check = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/'. $username));
          if (isset($username_check->uid) && $username_check->uid !== $user_json->uid) {
            $hasError = true;
            $userErr = "Username " . $username . " taken";
          }
        }

        // Check username
        if (empty($username)) { // Check empty field
          $hasError = true;
          $userErr = "Input required";
        } else if (preg_match("/[^A-Za-z0-9]/", $username)) { // Check username format
          $hasError = true;
          $userErr = "Invalid username format, please only use alphanumeric characters";
          array_push($error_arr, "Invalid Username: " . $username);
        } else { // Check availability
          $username_check = json_decode(ssl::get_content('http://172.25.76.76/api/team1/user/username/'. $username));
          if (isset($username_check->uid) && $username_check->uid !== $user_json->uid) {
            $hasError = true;
            $userErr = "Username " . $username . " taken";
          }
        }

        // Check first name
        if (empty($fname)) { //Check empty field
          $hasError = true;
          $fnameErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $fname)) { // Check firstname format
            $hasError = true;
            $fnameErr = "Invalid first name format, please only use alphabets";
            array_push($error_arr, "Invalid First Name: " . $fname);
        }

        // Check last name
        if (empty($lname)) { //Check empty field
          $hasError = true;
          $lnameErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $lname)) { // Check last name format
            $hasError = true;
            $lnameErr = "Invalid first name format, please only use alphabets";
            array_push($error_arr, "Invalid Last Name: " . $lname);
        }

        // DOB
        if (empty($dob)) {
            $hasError = true;
            $dobErr = "Input required";
        } if (strlen($dob) != 10 || !preg_match("/^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/", $dob)) { // Perform initial format check
            $hasError = true;
            $dobErr = "Invalid date";
            array_push($error_arr, "Invalid DOB: " . $dob);
        } else { // Check cleared. Now to determine state of leap year.
            $year = substr($dob, 0, 4);
            $month = substr($dob, 5, 2);
            $day = substr($dob, 8, 2);
            
            if ($month === "02" && $day === "29") { // If it is feb 29, check for valid year
                if (!((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)))) { // If not valid year
                    $hasError = true;
                    $dobErr = "Invalid date";
                }
            }
        }
        
        // Check nationality
        if (empty($nationality)) { //Check empty field
          $hasError = true;
          $nationalityErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $nationality)) { // Check nationality format
            $hasError = true;
            $nationalityErr = "Invalid nationality format, please only use alphabets";
            array_push($error_arr, "Invalid Nationality: " . $nationality);
        }

        // Check ethnicity
        if (empty($ethnicity)) { //Check empty field
          $hasError = true;
          $ethnicityErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $ethnicity)) { // Check ethnicity format
            $hasError = true;
            $ethnicityErr = "Invalid ethnicity format, please only use alphabets";
            array_push($error_arr, "Invalid Ethnicity: " . $ethnicity);
        }

        // Drug Allergy
        $drugAllergy = ($drugAllergy == "1");

        // Check phone
        if (empty($phone0)) {
            $hasError = true;
            $phone0Err = "Input required";
        } else {
            if (isContactNumberInvalid($phone0)) {
                $hasError = true;
                $phone0Err = "Invalid input";
                array_push($error_arr, "Invalid Phone0: " . $phone0);
            }
        }

        if (empty($phone1)) {
            if (!empty($phone2)) {
                $hasError = true;
                $phone1Err = "Input required";
            } else {
                $phone1 = $phone2 = NULL;
            }
        } else {
            if (isContactNumberInvalid($phone1)) {
                $hasError = true;
                $phone1Err = "Invalid input";
                array_push($error_arr, "Invalid Phone1: " . $phone1);
            }
        }

        if (empty($phone2)) {
            $phone2 = NULL;
        } else {
            if (isContactNumberInvalid($phone2)) {
                $hasError = true;
                $phone2Err = "Invalid input";
                array_push($error_arr, "Invalid Phone2: " . $phone2);
            }
        }

        if (empty($addr0) || empty($zip0)) {
            if (empty($addr0) && empty($zip0)) {
                $hasError = true;
                $addr0Err = $zip0Err = "Input required";
            } else if (!empty($addr0)) {
                $hasError = true;
                $addr0 = "Please input address for corresponding zipcode";
            } else {
                $hasError = true;
                $zip0 = "Please input zipcode for corresponding address";
            }
        } else {
            if (isAddressInvalid($addr0)) {
                $hasError = true;
                $addr0Err = "Invalid address";
                array_push($error_arr, "Invalid Address0: " . $addr0);
            }
            if (isZipcodeInvalid($zip0)) {
                $hasError = true;
                $zip0Err = "Invalid input";
                array_push($error_arr, "Invalid Zipcode0: " . $zip0);
            }
        }

        if (empty($addr1)) {
            if (!empty($addr2)) {
                $hasError = true;
                $addr1Err = "Input required";
            } else {
                $addr1 = $addr2 = NULL;
            }
        } else {
            if (isAddressInvalid($addr1)) {
                $hasError = true;
                $addr1Err = "Invalid input";
                array_push($error_arr, "Invalid Address1: " . $addr1);
            }
        }

        if (empty($addr2)) {
            $addr2 = NULL;
        } else {
            if (isAddressInvalid($addr2)) {
                $hasError = true;
                $addr2Err = "Invalid input";
                array_push($error_arr, "Invalid Address2: " . $addr2);
            }
        }

        if (empty($zip1)) {
            if (!empty($zip2)) {
                $hasError = true;
                $zip1Err = "Input required";
            } else {
                $zip1 = $zip2 = 0;
            }
        } else {
            if (isZipcodeInvalid($zip1)) {
                $hasError = true;
                $zip1Err = "Invalid input";
                array_push($error_arr, "Invalid Zipcode1: " . $zip1);
            }
        }

        if (empty($zip2)) {
            $zip2 = 0;
        } else {
            if (isZipcodeInvalid($zip2)) {
                $hasError = true;
                $zip2Err = "Invalid input";
                array_push($error_arr, "Invalid Zipcode2: " . $zip2);
            }
        }

        $zip0 = intval($zip0);
        $zip1 = intval($zip1);
        $zip2 = intval($zip2);

        $changed = particulars_changed($user_json, $username, $fname, $lname, $dob, $nationality, $ethnicity, $drugAllergy, $phone0, $phone1, $phone2, $addr0, $addr1, $addr2, $zip0, $zip1, $zip2);
        if (count($changed) == 0) {
            $hasError = true;
        }

        if (!$hasError) {
            $settings_save = true;
            $changed = particulars_changed($user_json, $username, $fname, $lname, $dob, $nationality, $ethnicity, $drugAllergy, $phone0, $phone1, $phone2, $addr0, $addr1, $addr2, $zip0, $zip1, $zip2);
            $particulars_json = json_array($user_json->uid, $username, $user_json->password, $user_json->salt, $fname, $lname, $user_json->nric, $dob, $user_json->sex, $phone0, $phone1, $phone2, $addr0, $addr1, $addr2, $zip0, $zip1, $zip2, $user_json->qualify, $user_json->bloodtype, $user_json->secret, $drugAllergy, $ethnicity, $nationality);
            $description = "Updated " . implode(", ", $changed);
            Log::recordTX($user_json->uid, "Info", $description);
            $url = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/update';
            $post_result = json_decode(ssl::post_content($url, $particulars_json, array('Content-Type: application/json')));
            if ($post_result->result) {
                Log::recordTX($user_json->uid, "Info", $description);
            } else {
                Log::recordTX($user_json->uid, "Error", "Error occured when updating particulars");
            }
            $user_json = json_decode(ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/'.$result->uid));
        } else {
            Log::recordTX($user_json->uid, "Error", implode(", ", $error_arr));
        }
    }



    function hasError() {
        $hasError = true;
    }

    function isContactNumberInvalid($number) {
        if (!preg_match("/^((3|6|8)[0-9]|9[0-8])\d{6}$/", $number)) { //number not valid
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function isAddressInvalid($address) {
        if (!preg_match("/^[a-zA-Z0-9# -]{6,}$/", $address)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function isZipcodeInvalid($zipcode) {
        if (!preg_match("/^[0-9]{6}$/", $zipcode)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    function particulars_changed($user_json, $username, $fname, $lname, $dob, $nationality, $ethnicity, $drugAllergy, $phone1, $phone2, $phone3, $addr1, $addr2, $addr3, $zip1, $zip2, $zip3) {
        $changed = array();
        if ($username !== $user_json->username) { array_push($changed, "Username"); }
        if ($fname !== $user_json->firstname) { array_push($changed, "First Name"); }
        if ($lname !== $user_json->lastname) { array_push($changed, "Last Name"); }
        if ($dob !== $user_json->dob) { array_push($changed, "Date of Birth"); }
        if ($nationality !== $user_json->nationality) { array_push($changed, "Nationality"); }
        if ($ethnicity !== $user_json->ethnicity) { array_push($changed, "Ethnicity"); }
        if ($drugAllergy !== $user_json->drugAllergy) { array_push($changed, "Drug Allergy"); }
        if ($phone1 !== $user_json->phone[0] || $phone2 !== $user_json->phone[1] || $phone3 !== $user_json->phone[2]) { array_push($changed, "Contact information"); }
        if ($addr1 !== $user_json->address[0] || $addr2 !== $user_json->address[1] || $addr3 !== $user_json->address[2]) { array_push($changed, "Address"); }
        if ($zip1 !== $user_json->zipcode[0] || $zip2 !== $user_json->zipcode[1] || $zip3 !== $user_json->zipcode[2]) { array_push($changed, "Zipcode"); }
        return $changed;
    }
    
    function json_array($uid, $username, $password, $salt, $fname, $lname, $nric, $dob, $sex, $phone1, $phone2, $phone3, $addr1, $addr2, $addr3, $zip1, $zip2, $zip3, $qualify, $bloodtype, $secret, $drugAllergy, $ethnicity, $nationality) {
        $arr = array(
            'uid' => $uid,
            'username' => $username,
            'password' => $password,
            'salt' => $salt,
            'firstName' => $fname,
            'lastName' => $lname,
            'nric' => $nric,
            'dob' => $dob,
            'sex' => $sex,
            'phone' => array($phone1, $phone2, $phone3),
            'address' => array($addr1, $addr2, $addr3),
            'zipcode' => array($zip1, $zip2, $zip3),
            'qualify' => $qualify,
            'bloodtype' => $bloodtype,
            'secret' => $secret,
            'drugAllergy' => $drugAllergy,
            'ethnicity' => $ethnicity,
            'nationality' => $nationality
        );
        return json_encode($arr);
    }

?>

<html>
<meta charset="utf-8">

    <head>
        <title>Edit your profile</title>
        <link href="css/main.css" rel="stylesheet">
    </head>

    <body>
        <?php include 'sidebar.php' ?>
        <div class="shifted">
            <h1>Edit your profile</h1>
            <hr style="margin-top:-15px">
            <form class="profile-form" name="profile-form" method="post" action="update.php">
                <?php if ($settings_save) { ?>
                    <script>alert("Your particulars have been updated!")</script> 
                <?php } ?>
                <div class="profile-update"><a href="changepass.php" style="text-decoration:none; color:blue">Click here to change password</a>
                </div>
                <div class="profile-update">Username: <span class="error-message"><?php echo empty($userErr) ? "" : "*" . $userErr ?></span><br>
                    <input name="input-username" type="text" placeholder="User"
                        value="<?php echo (isset($user_json->username) ? $user_json->username : "" )?>"><br>
                </div>
                <div class="profile-update">First Name: <span class="error-message"><?php echo empty($fnameErr) ? "" : "*" . $fnameErr ?></span><br>
                    <input name="input-firstname" type="text" placeholder="First Name" 
                        value="<?php echo (isset($user_json->firstname) ? $user_json->firstname : "" )?>"><br>
                </div>
                <div class="profile-update">Last Name: <span class="error-message"><?php echo empty($lnameErr) ? "" : "*" . $lnameErr ?></span><br>
                    <input name="input-lastname" type="text" placeholder="Last Name"
                        value="<?php echo (isset($user_json->lastname) ? $user_json->lastname : "" )?>"><br>
                </div>
                <div class="profile-update">Date of Birth: <span class="error-message"><?php echo empty($dobErr) ? "" : "*" . $dobErr ?></span><br>
                    <input type="date" name="input-dob" 
                        value="<?php echo (isset($user_json->dob) ? $user_json->dob : "" )?>"><br>
                </div>

                <div class="profile-update">Nationality: <span class="error-message"><?php echo empty($nationalityErr) ? "" : "*" . $nationalityErr ?></span><br>
                    <input type="text" name="input-nationality" 
                        value="<?php echo (isset($user_json->nationality) ? $user_json->nationality : "" )?>"><br>
                </div>
                <div class="profile-update">Ethnicity: <span class="error-message"><?php echo empty($ethnicityErr) ? "" : "*" . $ethnicityErr ?></span><br>
                    <input type="text" name="input-ethnicity" 
                        value="<?php echo (isset($user_json->ethnicity) ? $user_json->ethnicity : "" )?>"><br>
                </div>
                <div class="profile-update">Drug Allergy:<br><br><br>
                    <select name="input-drugAllergy">
                        <option <?php echo ($user_json->drugAllergy) ? "selected" : ""?> value="1">Yes</option>
                        <option <?php echo !($user_json->drugAllergy) ? "selected" : ""?> value ="0">No</option>
                    </select>
                </div>

                <?php for ($i = 0; $i < $num_phone; $i++) { ?>
                    <div class="profile-update">Phone <?php echo $i === 0 ? "" : $i ?>: <span class="error-message"><?php echo empty(${'phone'.$i.'Err'}) ? "" : "*" . ${'phone'.$i.'Err'} ?></span><br>
                    <input type="text" name="<?php echo 'input-phone'.$i ?>"
                        value="<?php echo (isset($user_json->phone) ? $user_json->phone[$i] : "" )?>"><br>
                    </div>
                <?php } ?>

                <?php for ($i = 0; $i < $num_address; $i++) { ?>
                    <div class="profile-update">Address <?php echo $i === 0 ? "" : $i ?>: <span class="error-message"><?php echo empty(${'addr'.$i.'Err'}) ? "" : "*" . ${'addr'.$i.'Err'} ?></span><br>
                    <input type="text" name="<?php echo 'input-address'.$i ?>"
                        value="<?php echo (isset($user_json->address) ? $user_json->address[$i] : "" )?>"><br>
                    </div>
                    <div class="profile-update">Zipcode <?php echo $i === 0 ? "" : $i ?>: <span class="error-message"><?php echo empty(${'zip'.$i.'Err'}) ? "" : "*" . ${'zip'.$i.'Err'} ?></span><br>
                        <input type="text" name="<?php echo 'input-zipcode'.$i ?>"
                            value="<?php echo (isset($user_json->zipcode) ? $user_json->zipcode[$i] : "" )?>"><br>
                    </div>
                <?php } ?>

                <div class="profile-update"><input class="profile-submit" type="submit" id="btn-login" name="login" class="btn-login" value="Save"></div>
            </form>
        </div>
    </body>


</html>
