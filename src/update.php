<?php

    include_once 'util/jwt.php';
    $result = WebToken::verifyToken($_COOKIE["jwt"], "dummykey");

    $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
    $user_type = $result->istherapist ? "therapist" : "patient";

    $user = "User";
    $first_name = "First Name";
    $last_name = "Last Name";

    $num_phone = count($user_json->phone);
    $num_address = count($user_json->address);
    $settings_save = false;
    $hasError = false;
    
    // Input
    $username = $fname = $lname = $dob = $phone0 = $phone1 = $phone2 = $addr0 = $addr1 = $addr2 = $zip0 = $zip1 = $zip2 = "";

    // Error messages
    $userErr = $fnameErr = $lnameErr = $dobErr = $phone0Err = $phone1Err = $phone2Err = $addr0Err = $addr1Err = $addr2Err = $zip0Err = $zip1Err = $zip2Err = "";

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
          $username_check = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/username/'. $username));
          if (isset($username_check->uid) && $username_check->uid !== $user_json->uid) {
            $hasError = true;
            $userErr = "Username unavailable";
          }
        }

        // Check first name
        if (empty($fname)) { //Check empty field
          $hasError = true;
          $fnameErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $fname)) { // Check firstname format
            $hasError = true;
            $fnameErr = "Invalid first name format, please only use alphabets";
        }

        // Check last name
        if (empty($lname)) { //Check empty field
          $hasError = true;
          $lnameErr = "Input required";
        } else if (preg_match("/[^A-Za-z]/", $lname)) { // Check last name format
            $hasError = true;
            $lnameErr = "Invalid first name format, please only use alphabets";
        }

/*
        // Check DOB
        if (empty($dob)) { // Check empty field
            $hasError = true;
            $dobErr = "Input required";
        } else (strlen($dob) != 10 OR !preg_match("/^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/", $dob)) { // Check date format
            $hasError = true;
            $dobErr = "Invalid date";
        } else { // Check for leap year
            $year = substr($dob, 0, 4);
            $month = substr($dob, 5, 2);
            $day = substr($dob, 8, 2);
            if ($month === "02" && $day === "29") {
                if (!((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)))) { //if not valid year
                    $hasError = true;
                    $dobErr = "Invalid date";
                }
            }  
        } */

        // Check phone
        if (empty($phone0)) {
            $hasError = true;
            $phone0Err = "Input required";
        } else {
            if (isContactNumberInvalid($phone0)) {
                $hasError = true;
                $phone0Err = "Invalid input";
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
            }
        }

        if (empty($phone2)) {
            $phone2 = NULL;
        } else {
            if (isContactNumberInvalid($phone2)) {
                $hasError = true;
                $phone2Err = "Invalid input";
            }
        }
        /*
        $addr0 = str_replace(" ", "%20", $addr0);
        $addr1 = str_replace(" ", "%20", $addr1);
        $addr2 = str_replace(" ", "%20", $addr2);
        */
	/*
        if (!$hasError) {
          $settings_save = true;
          $change_result = update_particulars($user_json->uid, $username, $user_json->password, $user_json->salt, $fname, $lname, $user_json->nric, $dob, $user_json->gender, $phone0, $phone1, $phone2, $addr0, $addr1, $addr2, $zip0, $zip1, $zip2, $user_json->qualify, $user_json->bloodtype, $user_json->nfcid, $user_json->secret);
          $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/1'));
        }
	*/
        
        if (!$hasError) {
            $particulars_json = json_array($user_json->uid, $username, $user_json->password, $user_json->salt, $fname, $lname, $user_json->nric, $dob, $user_json->gender, $phone0, $phone1, $phone2, $addr0, $addr1, $addr2, $zip0, $zip1, $zip2, $user_json->qualify, $user_json->bloodtype, $user_json->nfcid, $user_json->secret);
            $url = 'http://172.25.76.76/api/team1/user/update';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $particulars_json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_exec($ch);
            $user_json = json_decode(file_get_contents('http://172.25.76.76/api/team1/user/uid/' . $result->uid));
        }
        
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
    
    function json_array($uid, $username, $password, $salt, $fname, $lname, $nric, $dob, $gender, $phone1, $phone2, $phone3, $addr1, $addr2, $addr3, $zip1, $zip2, $zip3, $qualify, $bloodtype, $nfcid, $secret) {
        $arr = array(
            'uid' => $uid,
            'username' => $username,
            'password' => $password,
            'salt' => $salt,
            'firstName' => $fname,
            'lastName' => $lname,
            'nric' => $nric,
            'dob' => $dob,
            'gender' => $gender,
            'phone' => array($phone1, $phone2, $phone3),
            'address' => array($addr1, $addr2, $addr3),
            'zipcode' => array($zip1, $zip2, $zip3),
            'qualify' => $qualify,
            'bloodtype' => $bloodtype,
            'nfcid' => $nfcid,
            'secret' => $secret
        );
        return json_encode($arr);
    }
    
    function update_particulars($uid, $username, $password, $salt, $fname, $lname, $nric, $dob, $gender, $phone1, $phone2, $phone3, $addr1, $addr2, $addr3, $zip1, $zip2, $zip3, $qualify, $bloodtype, $nfcid) {
        
        //$change_result = json_decode(file_get_contents('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/update/'.$uid.'/'.$username.'/'.$password.'/'.$salt.'/'.$fname.'/'.$lname.'/'.$nric.'/'.$dob.'/'.$gender.'/'.$phone1.'/'.$phone2.'/'.$phone3.'/'.$addr1.'/'.$addr2.'/'.$addr3.'/'.$zip1.'/'.$zip2.'/'.$zip3.'/'.$qualify.'/'.$bloodtype.'/'.$nfcid.'/'));
        
        
        //$change_result = json_decode(file_get_contents('http://cs3205-4-i.comp.nus.edu.sg/api/team1/user/update/1/Bob99/$2y$10$rV.EgHEAFwc1NZQTncxdi.HTGK9DNDWkjjQ9cHfDk4aoapDUqhPVm/$2y$10$yLsZ4j4efAU5.4JJzBDgbO/Bobby/Mike/S1234567Z/2000-12-01/M/98989898/97979797/96969696/Kent%20Ridge/PGP/Sentosa%20Cove/555555/544444/533333/0/B+/123/'));
        
        //return $change_result->result;
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
            <form class="profile-form" name="profile-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <?php if ($settings_save) { ?>
                    <script>alert("Your particulars have been updated!")</script> 
                <?php } ?>
                <div class="profile-update">Username: <span class="error-message"><?php echo empty($userErr) ? "" : "*" . $userErr ?></span><br>
                    <input name="input-username" type="text" placeholder="<?php echo $user ?>"
                        value="<?php echo (isset($user_json->username) ? $user_json->username : "" )?>"><br>
                </div>
                <div class="profile-update">Password:<br>
                    <input name="input-password" type="password" placeholder="Password"><br>
                </div>
                <div class="profile-update">First Name: <span class="error-message"><?php echo empty($fnameErr) ? "" : "*" . $fnameErr ?></span><br>
                    <input name="input-firstname" type="text" placeholder="<?php echo $first_name ?>" 
                        value="<?php echo (isset($user_json->firstname) ? $user_json->firstname : "" )?>"><br>
                </div>
                <div class="profile-update">Last Name: <span class="error-message"><?php echo empty($lnameErr) ? "" : "*" . $lnameErr ?></span><br>
                    <input name="input-lastname" type="text" placeholder="<?php echo $last_name ?>"
                        value="<?php echo (isset($user_json->lastname) ? $user_json->lastname : "" )?>"><br>
                </div>
                <div class="profile-update">Date of Birth: <span class="error-message"><?php echo empty($dobErr) ? "" : "*" . $dobErr ?></span><br>
                    <input type="date" name="input-dob" 
                        value="<?php echo (isset($user_json->dob) ? $user_json->dob : "" )?>"><br>
                </div>
                <?php for ($i = 0; $i < $num_phone; $i++) { ?>
                    <div class="profile-update">Phone <?php echo $i === 0 ? "" : $i ?>: <span class="error-message"><?php echo empty(${'phone'.$i.'Err'}) ? "" : "*" . ${'phone'.$i.'Err'} ?></span><br>
                    <input type="text" name="<?php echo 'input-phone'.$i ?>"
                        value="<?php echo (isset($user_json->phone) ? $user_json->phone[$i] : "" )?>"><br>
                    </div>
                <?php } ?>

                <?php for ($i = 0; $i < $num_address; $i++) { ?>
                    <div class="profile-update">Address:  <?php echo $i === 0 ? "" : $i ?><br>
                    <input type="text" name="<?php echo 'input-address'.$i ?>"
                        value="<?php echo (isset($user_json->address) ? $user_json->address[$i] : "" )?>"><br>
                    </div>
                    <div class="profile-update">Zipcode:  <?php echo $i === 0 ? "" : $i ?><br>
                        <input type="text" name="<?php echo 'input-zipcode'.$i ?>"
                            value="<?php echo (isset($user_json->zipcode) ? $user_json->zipcode[$i] : "" )?>"><br>
                    </div>
                <?php } ?>

                <div class="profile-update"><input class="profile-submit" type="submit" id="btn-login" name="login" class="btn-login" value="Save"></div>
            </form>
        </div>
    </body>


</html>
