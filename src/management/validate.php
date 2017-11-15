<?php
	//validate.php: Checks submitted form values and react accordingly.
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/jwt-admin.php';
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/ssl.php';
	include_once $_SERVER["DOCUMENT_ROOT"] . '/util/csrf.php';
	
	WebToken::verifyToken($_COOKIE["jwt"]);
	
	session_start();	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
	
	
	//GLOBAL VARIABLES DECLARATION:
	$errorsPresent = "NO"; //track whether are there any validation errors.
	
	//EDIT USER ERROR VARIABLE:
	$emptyField = FALSE;
	$invalidType = FALSE;
	$usernameErr = FALSE;
	$usernameExists = FALSE;
	$pwLengthErr = FALSE;
	$pwDiffErr = FALSE;
	$nationalityErr = FALSE;
	$nricInvalid = FALSE;
	$firstNameErr = FALSE;
	$lastNameErr = FALSE;
	$ethnicityErr = FALSE;
	$invalidGender = FALSE;
	$invalidBlood = FALSE;
	$invalidAllergyOption = FALSE;
	$dobErr = FALSE;
	$contact1Err = FALSE;
	$contact2Err = FALSE;
	$contact3Err = FALSE;
	$addr1Err = FALSE;
	$addr2Err = FALSE;
	$addr3Err = FALSE;
	$zip1Err = FALSE;
	$zip2Err = FALSE;
	$zip3Err = FALSE;
	
?>

<?php // THIS SECTION DEALS WITH THE FIELD VALIDATION CHECKS. IT COMPRISES OF SEVERAL FUNCTIONS.	
	
	/*
		checkEmptyFields($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Ensure that all the required fields are filled in.
			INPUT: $isAdd, Determine whether is it adding or editing.
					Also check all POST data fields that are required.
			OUTPUT: isAdd = TRUE: 
					Adds session variable $_SESSION['emptyField'] = TRUE if it contains other characters. Session variable NOT PRESENT if required fields are filled in.
					
					isAdd = FALSE:
					Use global variable $emptyField = TRUE instead of creating session variable.
	*/	
	function checkEmptyFields($isAdd) {
		global $errorsPresent;
		global $emptyField;
		
		if ($isAdd) {
			if (empty(trim($_POST['usertype'])) OR empty(trim($_POST['username'])) OR empty($_POST['password']) OR empty($_POST['cfmPassword']) OR empty(trim($_POST['nationality'])) OR empty(trim($_POST['NRIC'])) OR empty(trim($_POST['firstname'])) OR empty(trim($_POST['lastname'])) OR empty(trim($_POST['ethnic'])) OR empty(trim($_POST['gender'])) OR empty($_POST['bloodtype']) OR empty($_POST['allergy']) OR empty(trim($_POST['dob'])) OR empty(trim($_POST['contact1'])) OR empty(trim($_POST['address1'])) OR empty(trim($_POST['zipcode1']))) {
				$_SESSION['emptyField'] = TRUE; //failed check
				$errorsPresent = "YES";
			}
		} else { //similar statement, but exclude password fields.
			if (empty(trim($_POST['usertype'])) OR empty(trim($_POST['username'])) OR empty(trim($_POST['nationality'])) OR empty(trim($_POST['NRIC'])) OR empty(trim($_POST['firstname'])) OR empty(trim($_POST['lastname'])) OR empty(trim($_POST['ethnic'])) OR empty(trim($_POST['gender'])) OR empty($_POST['bloodtype']) OR empty($_POST['allergy']) OR empty(trim($_POST['dob'])) OR empty(trim($_POST['contact1'])) OR empty(trim($_POST['address1'])) OR empty(trim($_POST['zipcode1']))) {
				$emptyField = TRUE;
				$errorsPresent = "YES";
			}
		}
	}
	
	/*
		checkUserType($isAdd):
			FUNCTIONALITY: Perform user type check. Since this field has only 2 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['usertype'].
			OUTPUT: $isAdd = TRUE: 
					Adds session variable $_SESSION['invalidType'] = TRUE if value is neither 'Patient' nor 'Therapist'.
					
					isAdd = FALSE:
					Use global variable $invalidType = TRUE instead of creating session variable.
	*/	
	function checkUserType($isAdd) {
		global $errorsPresent;
		global $invalidType;
		
		if (!($_POST['usertype'] === "Therapist" || $_POST['usertype'] === "Patient")) {
			$errorsPresent = "YES"; //triggers the variable to true.
			if ($isAdd) {
				$_SESSION['invalidType'] = TRUE;
			} else {
				$invalidType = TRUE;
			}
		}
	}
		
	/*
		checkUserName($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Ensure that username only contains alphanumeric characters.
				2) Whether username already exists in database (NOTE: NOT IMEPLEMETED YET).
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['username'].
			OUTPUT: $isAdd = TRUE: 
					Adds session variable $_SESSION['usernameErr'] = TRUE if it contains other characters. Session variable NOT PRESENT if username only contains alphanumeric characters. If 1) passes and 2) fails, adds session variable $_SESSION['usernameExists'] = TRUE if username exists in database. Session variable NOT PRESENT if username does not exist.
					
					isAdd = FALSE: 
					Use global variables $usernameErr = TRUE and $usernameExists = TRUE instead of creating session variable.
	*/	
	function checkUserName($isAdd) {
		global $errorsPresent;
		global $usernameErr;
		global $usernameExists;
		
		//Invalid username check. If flagged, skip next check.
		if (preg_match("/[^A-Za-z0-9]/", $_POST['username'])) {
			if ($isAdd) {
				$_SESSION['usernameErr'] = TRUE;
			} else {
				$usernameErr = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		//Check whether username exists in DB.
		if (!isset($_SESSION['usernameErr']) || !$usernameErr) {
			$result = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/' . $_POST['username']);
			
			if ($result === FALSE) {
				if ($isAdd) {
					failedDatabaseConnection('add');
				} else {
					failedDatabaseConnection('edit');
				}
			}
			
			$decode = json_decode($result);
						
			if (isset($decode->uid)) {
				if ($isAdd) {
					$_SESSION['usernameExists'] = TRUE;
				}  else {
					$usernameExists = TRUE;
				}
				$errorsPresent = "YES";
			}
		}
	}
	
	/*
		checkPassword($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Password length is at least 8 characters.
				2) Password is entered correctly (check with 'confirm password' field).
			INPUT: $isAdd. Determines add or edit action, 
					also make use of $_POST['password'] AND $_POST['cfmPassword'].
			OUTPUT: isAdd = TRUE: 
					Adds session variable $_SESSION['pwLengthErr'] = TRUE if pw length < 8 char.
					If that passes, adds session variable $_SESSION['pwDiffErr'] = TRUE if pw does not match. Session variables NOT PRESENT if it passes the checks.
					
					isAdd = FALSE: 
					Use global variables $pwLengthErr = TRUE and $pwDiffErr = TRUE instead of creating session variable.
	*/
	function checkPassword($isAdd) {
		global $errorsPresent;
		global $pwLengthErr;
		global $pwDiffErr;
		
		if (strlen($_POST['password']) < 8) {
			if ($isAdd) {				
				$_SESSION['pwLengthErr'] = TRUE;
			} else {
				$pwLengthErr = TRUE;
			}
			$errorsPresent = "YES";
			return;
		}
		
		if ($_POST['password'] != $_POST['cfmPassword']) {
			if ($isAdd) {				
				$_SESSION['pwDiffErr'] = TRUE;
			} else {
				$pwDiffErr = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkNRIC($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) NRIC/FIN entered is valid (syntax wise).
				2) Not reused (Not implemented. To be decided)
			INPUT: $isAdd. Determines add or edit action, 
					also make use of $_POST['NRIC'].
			OUTPUT: isAdd = TRUE:			
					Adds session variable $_SESSION['nricInvalid'] = TRUE if NRIC/FIN not valid.
					If that check passes, adds session variable $_SESSION['nricExists'] = TRUE if NRIC/FIN already exists in database (NOT IMPLEMENTED)
					Session variables NOT PRESENT if it passes the checks.
					
					isAdd = FALSE:
					Use global variables $nricInvalid = TRUE instead of creating session variable.
	*/
	function checkNRIC($isAdd) {
		global $errorsPresent;
		global $nricInvalid;
		
		if (!$isAdd) { //if we are editing user, skip this check.
			return;
		}
		
		$nric = $_POST['NRIC'];
		
		
		//Use this site to enter correct NRIC value: https://nric.biz/
		//Validation taken from: https://pgmmer.blogspot.sg/2009/12/singapore-nric-check.html with slight modfications
		
		// Invalid NRIC Check. If this fails, skip next check 
		$errorDetected = FALSE;
		$check = "";
		if ( preg_match('/^[ST][0-9]{7}[JZIHGFEDCBA]$/', $nric) ) { // NRIC
			$check = "JZIHGFEDCBA";
		} else if ( preg_match('/^[FG][0-9]{7}[XWUTRQPNMLK]$/', $nric) ) { // FIN
			$check = "XWUTRQPNMLK";
		} else {
			$errorDetected = TRUE;
		}
		
		if (!$errorDetected) {
			$total = $nric[1]*2 
						+ $nric[2]*7 
						+ $nric[3]*6 
						+ $nric[4]*5 
						+ $nric[5]*4 
						+ $nric[6]*3 
						+ $nric[7]*2;

			if ( $nric[0] == "T" OR $nric[0] == "G" ) {
				// shift 4 places for after year 2000
				$total = $total + 4;
			}
			
			if (! ($nric[8] == $check[$total % 11]) ) {
				$errorDetected = TRUE;
			}
		}
		
		if ($errorDetected) {
			if ($isAdd) {
				$_SESSION['nricInvalid'] = TRUE;
			} else {
				$nricInvalid = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		//TODO? : Duplicate NRIC Check. Not possible as no API call as of now.
	}
	
	/*
		checkNationality($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Nationality complies with the following regex: /^[A-Za-z ]+$/
			INPUT: $isAdd. Determines add or edit action, 
					also make use of $_POST['nationality'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['nationalityErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use global variables $nationalityErr = TRUE instead of creating session variable.
	*/
	function checkNationality($isAdd) {
		global $nationalityErr;
		global $errorsPresent;
		
		if (!preg_match("/^[A-Za-z ]+$/", $_POST['nationality'])) {
			if ($isAdd) {
				$_SESSION['nationalityErr'] = TRUE;
			} else {
				$nationalityErr = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	
	/*
		checkFirstAndLastName($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) First and Last Name String complies with the following regex: /^[\p{L}\s'.-]+$/
				For explanation of regex, see https://regex101.com/r/rT1exI/1
			INPUT: $isAdd. Determines add or edit action, 
					also make use of $_POST['firstname'] AND $_POST['lastname'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['[first(AND|OR)last]NameErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use global variables $[first(AND|OR)last]NameErr = TRUE instead of creating session variable.
	*/
	function checkFirstAndLastName($isAdd) {
		global $errorsPresent;
		global $firstNameErr;
		global $lastNameErr;
		
		if (!preg_match("/^[\p{L}\s'.-]+$/", $_POST['firstname'])) {
			if ($isAdd) {
				$_SESSION['firstNameErr'] = TRUE;
			} else {
				$firstNameErr = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if (!preg_match("/^[\p{L}\s'.-]+$/", $_POST['lastname'])) {
			if ($isAdd) {
				$_SESSION['lastNameErr'] = TRUE;
			} else {
				$lastNameErr = TRUE;
			}
			$errorsPresent = "YES";
		} 
	}
	
	/*
		checkEthnic($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Ethnicity complies with the following regex: /^[A-Za-z ]+$/
			INPUT: $isAdd. Determines add or edit action, 
					also make use of $_POST['ethnic'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['ethnicityErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use global variables $ethnicityErr = TRUE instead of creating session variable.
	*/
	function checkEthnic($isAdd) {
		global $ethnicityErr;
		global $errorsPresent;
		
		if (!preg_match("/^[A-Za-z ]+$/", $_POST['ethnic'])) {
			if ($isAdd) {
				$_SESSION['ethnicityErr'] = TRUE;
			} else {
				$ethnicityErr = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkGender($isAdd):
			FUNCTIONALITY: Perform gender value check. Since this field has only 2 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['gender'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['invalidGender'] = TRUE if value is invalid.
					
					$isAdd = FALSE:
					Use global variables $invalidGender = TRUE instead of creating session variable.
			
	*/	
	function checkGender($isAdd) {
		global $errorsPresent;
		global $invalidGender;
		
		if (!($_POST['gender'] === "M" || $_POST['gender'] === "F")) {
			if ($isAdd) {
				$_SESSION['invalidGender'] = TRUE;
			} else {
				$invalidGender = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkAllergy($isAdd):
			FUNCTIONALITY: Perform allergy value check. Since this field has only 2 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['allergy'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['invalidAllergyOption'] = TRUE if value is invalid.
					
					$isAdd = FALSE:
					Use global variables $invalidAllergyOption = TRUE instead of creating session variable.
			
	*/	
	function checkAllergy($isAdd) {
		global $errorsPresent;
		global $invalidAllergyOption;
		
		if (!($_POST['allergy'] === "Yes" || $_POST['allergy'] === "No")) {
			if ($isAdd) {
				$_SESSION['invalidAllergyOption'] = TRUE;
			} else {
				$invalidAllergyOption = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkBloodType($isAdd):
			FUNCTIONALITY: Performs blood type check. Since this field has only 8 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['bloodtype'].
			OUTPUT: $isAdd = TRUE:
					Adds session variable $_SESSION['invalidBlood'] = TRUE if value is not in the dropdown list.
					
					$isAdd = FALSE:
					Use global variables $invalidBlood = TRUE instead of creating session variable.					
	*/
	function checkBloodType($isAdd) {
		global $errorsPresent;
		global $invalidBlood;
		
		$bTypeArr = array("O+", "O-", "A+", "A-", "B+", "B-", "AB+", "AB-");
		
		if (!in_array($_POST['bloodtype'], $bTypeArr, TRUE)) {
			if ($isAdd) {
				$_SESSION['invalidBlood'] = TRUE; //failed check
			} else {
				$invalidBlood = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkDOB($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) If the date format supplied is valid (yyyy-mm-dd). Modern HTML5 browsers, except firefox, will definitely pass this as it supports type="date".
				
				The regex expression to check (URL: https://regex101.com/r/ChegrJ/1 ): /^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/
				
				Checks for the format, number of days in respective months. However, it does not check whether is it a leap year. Will perform manual check on it.
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['dob'].
			OUTPUT: $isAdd = TRUE: 
					Add session variable $_SESSION['dobErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use global variables $dobErr = TRUE instead of creating session variable.	
	*/
	function checkDOB($isAdd) {
		global $errorsPresent;
		global $dobErr;
		
		if (strlen($_POST['dob']) != 10 OR !preg_match("/^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/", $_POST['dob'])) { //perform initial format check
			if ($isAdd) { 
				$_SESSION['dobErr'] = TRUE;
			} else {
				$dobErr = TRUE;
			}
			$errorsPresent = "YES";
		} else { //check cleared. Now to determine state of leap year.
			$year = substr($_POST['dob'], 0, 4);
			$month = substr($_POST['dob'], 5, 2);
			$day = substr($_POST['dob'], 8, 2);
			
			if ($month === "02" && $day === "29") { //if it is feb 29, check for valid year
				if (!((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)))) { //if not valid year
					if ($isAdd) { 
						$_SESSION['dobErr'] = TRUE;
					} else {
						$dobErr = TRUE;
					}
					$errorsPresent = "YES";
				}
			}
		}
	}
	
	/*
		checkContactNumber($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Ensures that the value entered is valid for a contact number (8-digit numeric).
				2) If contact2 and contact3 are not empty, check them too.
			INPUT:  $isAdd. Determines add or edit action, also make use of $_POST['contact1'], $_POST['contact2'] and $_POST['contact3']
			OUTPUT: $isAdd = TRUE:
					Add relevant session variable(s) if check fails (see comments in function for listing). Session variable(s) NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use respective global variables instead of creating session variable.
	*/
	function checkContactNumber($isAdd) {
		global $errorsPresent;
		global $contact1Err;
		global $contact2Err;
		global $contact3Err;
		
		if (isContactNumberInvalid($_POST['contact1']) == TRUE) {
			if ($isAdd) {
				$_SESSION['contact1Err'] = TRUE; //sets the session variable for contact1's error
			} else {
				$contact1Err = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['contact2']) && isContactNumberInvalid($_POST['contact2'])){
			if ($isAdd) {
				$_SESSION['contact2Err'] = TRUE; //sets the session variable for contact2's error
			} else {
				$contact2Err = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['contact3']) && isContactNumberInvalid($_POST['contact3'])) {
			if ($isAdd) {
				$_SESSION['contact3Err'] = TRUE; //sets the session variable for contact3's error
			} else {
				$contact3Err = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		isContactNumberInvalid($number):
			FUNCTIONALITY: checkContactNumber() will call it. Passes the contact numbers to this method.
				1) Ensure that the phone number is valid with the following regex (URL: https://regex101.com/r/ChegrJ/4 ): /^((3|6|8)[0-9]|9[0-8])\d{6}$/
			INPUT: Phone Number ($number)
			OUTPUT: Returns TRUE if invalid, FALSE if not.
	*/
	function isContactNumberInvalid($number) {
		if (!preg_match("/^((3|6|8)[0-9]|9[0-8])\d{6}$/", $number)) { //number not valid
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/*
		checkAddress($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) Ensure that the address string is alphanumeric only, with space character, # and - as exceptions.
				2) The minimum length of string should be at least 6 (arbitary, can adjust when needed).
				3) If address2 and address3 are not empty, check them too.
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['address1'], $_POST['address2'] and $_POST['address3']
			OUTPUT: $isAdd = TRUE:
					Add relevant session variable(s) if check fails (see comments in function for listing). Session variable(s) NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use respective global variables instead of creating session variable.
	*/
	function checkAddress($isAdd) {
		global $errorsPresent;
		global $addr1Err;
		global $addr2Err;
		global $addr3Err;
		
		if (isAddressInvalid($_POST['address1']) == TRUE) {
			if ($isAdd) {
				$_SESSION['addr1Err'] = TRUE; //sets the session variable for address1's error
			} else {
				$addr1Err = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if ((!empty($_POST['address2']) && isAddressInvalid($_POST['address2'])) || (empty($_POST['address2']) && !empty($_POST['zipcode2']))){
			if ($isAdd) {
				$_SESSION['addr2Err'] = TRUE; //sets the session variable for address2's error
			} else {
				$addr2Err = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if ((!empty($_POST['address3']) && isAddressInvalid($_POST['address3'])) || (empty($_POST['address3']) && !empty($_POST['zipcode3']))){
			if ($isAdd) {
				$_SESSION['addr3Err'] = TRUE; //sets the session variable for address3's error
			} else {
				$addr3Err = TRUE;
			}
			$errorsPresent = "YES";
		}
	}
	
	/*
		isAddressInvalid($address):
			FUNCTIONALITY: checkAddress() will call it. Passes the address string to this method.
				1) Ensure that the address is valid with the following regex (URL: https://regex101.com/r/ChegrJ/6 ): /^[a-zA-Z0-9# -]{6,}$/
			INPUT: Address ($address)
			OUTPUT: Returns TRUE if invalid, FALSE if not.
	*/
	function isAddressInvalid($address) {
		if (!preg_match("/^[a-zA-Z0-9# -]{6,}$/", $address)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/*
		checkZip($isAdd):
			FUNCTIONALITY: Performs the following check:
				1) String consists of all numbers
				2) String is exactly 6 characters in length
				3) If address2 and address3 are not empty, perform checks on zipcode2 and zipcode3 too.
			INPUT: $isAdd. Determines add or edit action, also make use of $_POST['zipcode1'], $_POST['zipcode2'] and $_POST['zipcode3']
			OUTPUT: $isAdd = TRUE:
					Add relevant session variable(s) if check fails (see comments in function for listing). Session variable(s) NOT PRESENT if it passes the checks.
					
					$isAdd = FALSE:
					Use respective global variables instead of creating session variable.
	*/
	function checkZip($isAdd) {
		global $errorsPresent;
		global $zip1Err;
		global $zip2Err;
		global $zip3Err;
		
		if (isZipcodeInvalid($_POST['zipcode1']) == TRUE) {
			if ($isAdd) {
				$_SESSION['zip1Err'] = TRUE; //sets the session variable for zipcode1's error
			} else {
				$zip1Err = TRUE;
			}
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['address2'])) { //if address2 field is not empty, check zipcode2
			if (isZipcodeInvalid($_POST['zipcode2']) == TRUE) {
				if ($isAdd) {
					$_SESSION['zip2Err'] = TRUE; //sets the session variable for zipcode2's error
				} else {
					$zip2Err = TRUE;
				}
				$errorsPresent = "YES";
			}
		}
		
		if (!empty($_POST['address3'])) { //if address3 field is not empty, check zipcode3
			if (isZipcodeInvalid($_POST['zipcode3']) == TRUE) {
				if ($isAdd) {
					$_SESSION['zip3Err'] = TRUE; //sets the session variable for zipcode3's error
				} else {
					$zip3Err = TRUE;
				}
				$errorsPresent = "YES";
			}
		}
	}
	
	/*
		iZipcodeInvalid($address):
			FUNCTIONALITY: checkZip() will call it. Passes the zipcode string to this method.
				1) Ensure that the address is valid with the following regex: /^[0-9]{6}$/
			INPUT: zipcode ($zipcode)
			OUTPUT: Returns TRUE if invalid, FALSE if not.
	*/
	function isZipcodeInvalid($zipcode) {
		if (!preg_match("/^[0-9]{6}$/", $zipcode)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
?>

<?php // THIS SECTION HAS ONLY 1 FUNCTION - TO PRINT OUT THE ERROR MESSAGES ARISING FROM EDIT USER
		
	/*
		generateEditUserErrorMsg():
			FUNCTIONALITY: Method gets called if there are errors present. The error messages will be generated (and echoed) here.
			INPUT: NONE. Use the global variables.
			OUTPUT: Echos out the respective error messages.
	*/
	function generateEditUserErrorMsg() {
		global $emptyField;
		global $invalidType;
		global $usernameErr;
		global $usernameExists;
		global $pwLengthErr;
		global $pwDiffErr;
		global $nricInvalid;
		global $nationalityErr;
		global $firstNameErr;
		global $lastNameErr;
		global $ethnicityErr;
		global $invalidGender;
		global $invalidBlood;
		global $invalidAllergyOption;
		global $dobErr;
		global $contact1Err;
		global $contact2Err;
		global $contact3Err;
		global $addr1Err;
		global $addr2Err;
		global $addr3Err;
		global $zip1Err;
		global $zip2Err;
		global $zip3Err;
		
		echo '
	<div class="error">
		<table>
			<tr><td><img src="img/error.png" height="40" width="40"></td><td>Fix The Following Fields:</td></tr>
		</table>
		<table>'."\n";
		if ($invalidType) {
			echo "\t\t\t" . '<tr><td><b>Patient or Therapist:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
		}
		
		if ($usernameExists) {
			echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Username already exists.<br/></td></tr>' . "\n";
		}
		
		if ($usernameErr) {
			echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Only alphanumeric characters allowed.<br/></td></tr>' . "\n";
		}
		
		if ($pwLengthErr) {
			echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Minimum password length must be at least 8.<br/></td></tr>' . "\n";
		}
		
		if ($pwDiffErr) {
			echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Password fields do not match.<br/></td></tr>' . "\n";
		}
		
		if ($nationalityErr) {
			echo "\t\t\t" . '<tr><td><b>Nationality:</b>&emsp;Invalid character. Please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($nricInvalid) {
			echo "\t\t\t" . '<tr><td><b>NRIC/FIN:</b>&emsp;Invalid value entered. Please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($firstNameErr) {
			echo "\t\t\t" . '<tr><td><b>First Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($lastNameErr) {
			echo "\t\t\t" . '<tr><td><b>Last Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
	
		if ($ethnicityErr) {
			echo "\t\t\t" . '<tr><td><b>Ethnicity:</b>&emsp;Invalid character. Please re-enter.<br/></td></tr>' . "\n";
		}
	
		if ($invalidGender) {
			echo "\t\t\t" . '<tr><td><b>Gender:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
		}
		
		if ($invalidBlood) {
			echo "\t\t\t" . '<tr><td><b>Blood Type:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
		}
		
		if ($invalidAllergyOption) {
			echo "\t\t\t" . '<tr><td><b>Drug Allergy:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
		}
		
		if ($dobErr) {
			echo "\t\t\t" . '<tr><td><b>Date of Birth:</b>&emsp;Invalid birthdate, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($contact1Err) {
			echo "\t\t\t" . '<tr><td><b>Main Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($contact2Err) {
			echo "\t\t\t" . '<tr><td><b>Second Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($contact3Err) {
			echo "\t\t\t" . '<tr><td><b>Third Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($addr1Err) {
			echo "\t\t\t" . '<tr><td><b>Main Address:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($addr2Err) {
			echo "\t\t\t" . '<tr><td><b>Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($addr3Err) {
			echo "\t\t\t" . '<tr><td><b>Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($zip1Err) {
			echo "\t\t\t" . '<tr><td><b>Zipcode for Address 1:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($zip2Err) {
			echo "\t\t\t" . '<tr><td><b>Zipcode for Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($zip3Err) {
			echo "\t\t\t" . '<tr><td><b>Zipcode for Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
		}
		
		if ($emptyField) {
			echo "\t\t\t" . '<tr><td><b>Note:</b>&emsp;One or more required fields are empty. Ensure that they are filled up.<br/></td></tr>' . "\n";
		}
		
		echo '</table></div><br/>';
	}
?>

<?php // THIS SECTION REDIRECTS BACK TO THE CALLED PAGE IF DATABASE CONNECTION IS DOWN.
	// IT ONLY HAS ONE FUNCTION
	
	/*
		failedDatabaseConnection($mode):
			FUNCTIONALITY: Method gets called if DB connection failed.
			INPUT: $mode. Determines which sever input it came from (add, edit, delete)
			OUTPUT: Triggers relevant session error values and redirect back to the page it was previously.
	*/
	function failedDatabaseConnection($mode) {
		switch ($mode) {
			case 'delete':
				$_SESSION['printThirdArea'] = TRUE;
				$_SESSION['successfulDeletion'] = FALSE;
				header("location: console.php?navi=delete");
				exit();
				break;
			case 'add':
				$_SESSION['generateAddStatus'] = TRUE;
				$_SESSION['addUserSuccess'] = FALSE;
				header("location: console.php?navi=add");
				exit();
				break;
			case 'edit':
				$_SESSION['generateEditStatus'] = TRUE;
				$_SESSION['editUserSuccess'] = FALSE;
				
				//different from others as we are using AJAX
				echo "<script>window.location = 'console.php?navi=edit'</script>";
				exit();
				break;
		}
	}
?>

<?php // THIS SECTION DEALS WITH THE ACTUAL POST REQUEST THAT ARRIVE
	
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		echo "Go home, you are drunk.";
		exit();
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	    //get csrf token
	    $csrf = CSRFToken::getToken($_POST['csrf']);
	    if (isset($csrf->result) || $csrf->expiry < time() || $csrf->description != "admin_".$_POST['action'] || $csrf->uid != 0) {
	        //invalid csrf token
	        Log::recordTX(0, "Warning", "Invalid csrf when accessing validate.php");
	        header('HTTP/1.0 400 Bad Request.');
	        die();
	    } else {
			//delete token after usage.
			CSRFToken::deleteToken($_POST['csrf']);
		}
	}
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "delete") {
		
		//Navigation Session Check
		if ($_SESSION['latestAction'] !== "DELETE") {
			$_SESSION['naviError'] = TRUE;
			header("location: console.php");
			exit();
		}		
		
		//Attempt to retrieve user from database
		$validForDeletion = FALSE;
		$resultDel = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/' . $_POST['username']);
		
		if ($resultDel === FALSE) {
			failedDatabaseConnection('delete');
		}
		
		$decodeDel = json_decode($resultDel);		
		
		if (isset($decodeDel->uid)) {
			if (!($decodeDel->uid === -1 || $decodeDel->uid === 0)) {   //ignore uid 0 or -1. Not for manipulation.
				$validForDeletion = TRUE;
			}
		}
		
		if ($validForDeletion) {
			$_SESSION['validForDeletion'] = TRUE;
			$_SESSION['delUserName'] = $_POST['username'];
			$_SESSION['delUserID'] = $decodeDel->uid;  //for delete2 usage.
		} else {
			$_SESSION['validForDeletion'] = FALSE;
			//remove in the case of multiple delete tabs opened.
			unset($_SESSION['delUserID']);
			unset($_SESSION['delUserName']);
		}
		
		$_SESSION['printSecondArea'] = TRUE;
		header("location: console.php?navi=delete");
		exit();
		
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "delete2") {
		$readyToDelete = FALSE;
		
		//Navigation Session Check
		if ($_SESSION['latestAction'] !== "DELETE") {
			$_SESSION['naviError'] = TRUE;
			header("location: console.php");
			exit();
		}
		
		//Note: Have not implemented server side checking for ticked box 
		
		//Simple validation to ensure that it is really the selected user.
		//Also helps if multiple queried delete tabs are opened. Delete will fail if different users are queried.
		$cfmDel = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/' . $_POST['cfmUserName']);
		
		if ($cfmDel === FALSE) {
			failedDatabaseConnection('delete');
		}
		
		$cfmDelResult = json_decode($cfmDel);
		
		if (isset($cfmDelResult->uid) && ($cfmDelResult->uid === $_SESSION['delUserID'])) {
			$readyToDelete = TRUE;
		}
		
		if ($readyToDelete) {
			//Perform User Deletion
			$resultDel2 = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/delete/' 
				. $_SESSION['delUserID']);		
				
			if ($resultDel2 === FALSE) {
				failedDatabaseConnection('delete');
			}

			$decodeDel2 = json_decode($resultDel2);
			if ($decodeDel2 -> result === 1) { //deletion successful
				$_SESSION['successfulDeletion'] = TRUE;
			} else {
				$_SESSION['successfulDeletion'] = FALSE;
			}
		} else {
			//Illegal Action Detected
			$_SESSION['successfulDeletion'] = FALSE;
		}
		
		unset($_SESSION['delUserID']);
		$_SESSION['printThirdArea'] = TRUE;
		header("location: console.php?navi=delete");
		exit();
	}
		
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "edit1") {
		
		//Navigation Session Check
		if ($_SESSION['latestAction'] !== "EDIT") {
			$_SESSION['naviError'] = TRUE;
			header("location: console.php");
			exit();
		}
		// sleep for 1 second
		sleep(1);
				
		//Double-check from DB
		$connection = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/username/' . $_POST['editUserName']);
		
		if ($connection === FALSE) {
			failedDatabaseConnection('edit');
		}
		
		$result = json_decode($connection);
		
		//value received, php include 2nd form if user exists...
		if (isset($result->uid) && !(($result->uid === 0 || $result->uid === -1))) {  //ignore uid 0 or -1. Not for manipulation.
			$_SESSION['editUserID'] = $result->uid;
			include "edit-form.php";
		} else {
			echo '<br/><h3 class="errorDel">ERROR: username not found.</h3><br/>';
		}
		
		exit();
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "edit2") {
		
		//Navigation Session Check
		if ($_SESSION['latestAction'] !== "EDIT") {
			$_SESSION['naviError'] = TRUE;
			header("location: console.php");
			exit();
		}
		//sleep for 1 second
		sleep(1);
		
		//Check attempt to change username and password
		$isUsernameChanged = FALSE;
		$isPasswordChanged = FALSE;
				
		//Perform empty fields check. $emptyField
		checkEmptyFields(FALSE);
		
		//Checks for error after required fields are filled in.
		if ($errorsPresent === "NO") {
			
			//Retrieve Current info Again
		    if (strpos($_SESSION['editUserID'], '/') !== false) {
		        Log::recordTX($uid, "Error", "Unrecognised uid: " . $_SESSION['editUserID']);
				unset($_SESSION['editUserID']);
		        header('HTTP/1.0 400 Bad Request.');
		        die();
		    }
			$connection = @ssl::get_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $_SESSION['editUserID']);
			
			if ($connection === FALSE) {
				failedDatabaseConnection('edit');
			}
			
			$editCurrentinfo = json_decode($connection);
			
			//Check that at least 1 value is changed. If not display a message.			
			//Set up the variable
			$valuesChanged = FALSE;
			
			//Perform usertype check
			if (($editCurrentinfo->qualify == 1 && $_POST['usertype'] !== 'Therapist') 
				|| ($editCurrentinfo->qualify == 0 && $_POST['usertype'] !== 'Patient')) {
				$valuesChanged = TRUE;
				checkUserType(FALSE);
			}
			
			//Perform username check if username is modified.	
			if ($editCurrentinfo->username !== $_POST['username']) {
				$valuesChanged = TRUE;
				$isUsernameChanged = TRUE;
				checkUserName(FALSE);
			}
			
			//Perform password check if password field is filled.
			if (!empty($_POST['password'])) {
				$valuesChanged = TRUE;
				$isPasswordChanged = TRUE;
				checkPassword(FALSE);
			}
		
			//Trigger to detect Nationality value is changed.
			if ($editCurrentinfo->nationality !== $_POST['nationality'] ) {
				$valuesChanged = TRUE;
				checkNationality(FALSE);
			}
		
			//Perform NRIC check if it is modified.
			if ($editCurrentinfo->nric !== $_POST['NRIC'] ) {
				$valuesChanged = TRUE;
				checkNRIC(FALSE);
			}
			
			//Perform firstname and lastname check if either or both is modified.
			if ($editCurrentinfo->firstname !== $_POST['firstname'] 
				|| $editCurrentinfo->lastname !== $_POST['lastname']) {
				$valuesChanged = TRUE;
				checkFirstAndLastName(FALSE);
			}
			
			//Trigger to detect Ethnicity value is changed. No validation check
			if ($editCurrentinfo->ethnicity !== $_POST['ethnic'] ) {
				$valuesChanged = TRUE;
				checkEthnic(FALSE);
			}
			
			//Perform gender field check if it is modified.
			if ($editCurrentinfo->sex !== $_POST['gender']) {	
				$valuesChanged = TRUE;
				checkGender(FALSE);
			}
			
			//Perform blood type check if it is modified.
			if ($editCurrentinfo->bloodtype !== $_POST['bloodtype']) {
				$valuesChanged = TRUE;
				checkBloodType(FALSE);
			}
			
			//Perform allergy type check if it is modified.
			$drugAllergyTxt = "";
			if ($editCurrentinfo->drugAllergy) {
				$drugAllergyTxt = "Yes";
			} else {
				$drugAllergyTxt = "No";
			}
			
			if ($drugAllergyTxt !== $_POST['allergy']) {
				$valuesChanged = TRUE;
				checkAllergy(FALSE);
			}
		
			//Perform dob check if it is modified
			if ($editCurrentinfo->dob !== $_POST['dob']) {
				$valuesChanged = TRUE;
				checkDOB(FALSE);
			}
		
			//Perform contact number checks if it is modifed
			$updatedPhone = array($_POST['contact1'], $_POST['contact2'], $_POST['contact3']);
			
			for($i = 0; $i < 3; $i++) {
				if (empty($updatedPhone[$i]) && $i != 0) {
					$updatedPhone[$i] = NULL;
				}
				
				if ($updatedPhone[$i] !== $editCurrentinfo->phone[$i]) {
					$valuesChanged = TRUE;
					checkContactNumber(FALSE);
					break;
				}
			}
		
			//Perform address checks if it is modified
			$updatedAddr = array($_POST['address1'], $_POST['address2'], $_POST['address3']);
			
			for($i = 0; $i < 3; $i++) {
				if (empty($updatedAddr[$i]) && $i != 0) {
					$updatedAddr[$i] = NULL;
				}
				
				if ($updatedAddr[$i] !== $editCurrentinfo->address[$i]) {
					$valuesChanged = TRUE;
					checkAddress(FALSE);
					break;
				}
			}
		
			//Perform postal code checks if it is modified
			$updatedZip = array($_POST['zipcode1'], $_POST['zipcode2'], $_POST['zipcode3']);
			
			for($i = 0; $i < 3; $i++) {
				if ($editCurrentinfo->zipcode[$i] === 0) {
					$editCurrentinfo->zipcode[$i] = "";
				}
				$stringValue = $editCurrentinfo->zipcode[$i];
				if ($updatedZip[$i] !== "$stringValue") {
					$valuesChanged = TRUE;
					checkZip(FALSE);
					break;
				}
			}
		}
		
		//ERROR Table, perform if statements to generate.
		if ($errorsPresent === "YES") {
			generateEditUserErrorMsg();	
		} else { //If form haz no errors.
			if (!$valuesChanged) {
				echo "<br/><h2>NOTICE: The values are not changed.</h2><br/>";
			} else { //Success. Prepare to update DB.
			
				//Setup new variables to pass to DB
				$newUserName;
				$newPassword;
				$newSalt;
				$newPhone;
				$newAddress;
				$newZipCode;
				$newQualify;
				
				if ($isUsernameChanged) {
					$newUserName = $_POST['username'];
				} else {
					$newUserName = $editCurrentinfo->username;
				}
				
				if ($isPasswordChanged) {
					//Separate salt from password, perform SHA256 on bcrypt hash too.
					$newPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
					//first 29 characters of bcrypt hash is the salt.
					$newSalt = substr($newPassword, 0, 29);
					//finally, SHA256 the bcrypt string.
					$newPassword = hash('SHA256', $newPassword);
				} else {
					$newPassword = $editCurrentinfo->password;
					$newSalt = $editCurrentinfo->salt;
				}
				
				//Handle Drug Allergy
				$drugAllergy = FALSE;
				if ($_POST['allergy'] === "Yes" ) {
					$drugAllergy = TRUE;
				}
				
				//Set up array of phone numbers (Handle empty fields too)
				$newPhone = array($_POST['contact1']);
			
				if (!empty(trim($_POST['contact2']))) {
					array_push($newPhone, $_POST['contact2']);
				} else {
					array_push($newPhone, NULL);
				}
			
				if (!empty(trim($_POST['contact3']))) {
					array_push($newPhone, $_POST['contact3']);
				} else {
					array_push($newPhone, NULL);
				}
				
				//Set up array of addresses (Handle empty fields too)
				$newAddress = array($_POST['address1']);
			
				if (!empty(trim($_POST['address2']))) {
					array_push($newAddress, $_POST['address2']);
				} else {
					array_push($newAddress, NULL);
				}
			
				if (!empty(trim($_POST['address3']))) {
					array_push($newAddress, $_POST['address3']);
				} else {
					array_push($newAddress, NULL);
				}
				
				//Set up array of zip codes (Handle empty fields too)
				$newZipCode = array(intval($_POST['zipcode1']));
			
				if (!empty(trim($_POST['zipcode2']))) {
					array_push($newZipCode, intval($_POST['zipcode2']));
				} else {
					array_push($newZipCode, 0);
				}
			
				if (!empty(trim($_POST['zipcode3']))) {
					array_push($newZipCode, intval($_POST['zipcode3']));
				} else {
					array_push($newZipCode, 0);
				}
				
				//Check therapist value. 1 = TRUE, 0 = FALSE.
				$newQualify = 0;
			
				if ($_POST['usertype'] === "Therapist" ) {
					$newQualify = 1;
				}
				
				//prepare edit db statement and execute POST.....
				$updateToDB = array (
					"username"		=> $newUserName,
					"password"		=> $newPassword,
					"salt" 			=> $newSalt,
					"firstName"		=> $_POST['firstname'],
					"lastName"		=> $_POST['lastname'],
					"ethnicity"		=> $_POST['ethnic'],
					"nationality"	=> $_POST['nationality'],
					"nric"			=> $_POST['NRIC'],
					"dob"			=> $_POST['dob'],
					"sex"			=> $_POST['gender'],
					"phone"			=> $newPhone,
					"address"		=> $newAddress,
					"zipcode"		=> $newZipCode,
					"qualify"		=> $newQualify,		//this is NOT a string value.
					"bloodtype"		=> $_POST['bloodtype'],
					"drugAllergy"	=> $drugAllergy,
					"secret"		=> "someSecretLUL",		//Stub. Will update this.
					"nfcid"			=> NULL,				//Stub(?)
					"uid"			=> $editCurrentinfo->uid
				);
				
				$updateToDB_json = json_encode($updateToDB);
								
				//Establish connection to DB server and get result.
				$connectionEdit = @ssl::post_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/update', 
										$updateToDB_json, array('Content-Type: application/json', 'Content-Length: ' . strlen($updateToDB_json)));
			
				if ($connectionEdit === FALSE) {
					failedDatabaseConnection('edit');
				}
			
				$decodeEdit = json_decode($connectionEdit);
				
				//Result handling.
				$_SESSION['generateEditStatus'] = TRUE;
				$_SESSION['editUserSuccess'] = FALSE;
				
				if ($decodeEdit->result == 1) {
					$_SESSION['editUserSuccess'] = TRUE;
				}
				
				unset($_SESSION['editUserID']);
				echo "<script>window.location = 'console.php?navi=edit'</script>";
				exit();	
			}
		}
		exit();
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "add") {
		
		//Navigation Session Check
		if ($_SESSION['latestAction'] !== "ADD") {
			$_SESSION['naviError'] = TRUE;
			header("location: console.php");
			exit();
		}		
		
		//Perform empty fields check
		checkEmptyFields(TRUE);
		
		//Checks for error after required fields are filled in.
		if ($errorsPresent === "NO") {
			//Perform usertype check
			checkUserType(TRUE);
			
			//Perform username check
			checkUserName(TRUE);
		
			//Perform password check
			checkPassword(TRUE);
			
			//Perform nationality check
			checkNationality(TRUE);
		
			//Perform NRIC check
			checkNRIC(TRUE);
			
			//Perform firstname check
			checkFirstAndLastName(TRUE);
			
			//Perform ethnicity check
			checkEthnic(TRUE);
			
			//Perform gender field check
			checkGender(TRUE);
			
			//Perform blood type check
			checkBloodType(TRUE);
			
			//Perform allergy field check
			checkAllergy(TRUE);
		
			//Perform dob check
			checkDOB(TRUE);
		
			//Perform contact number checks
			checkContactNumber(TRUE);
		
			//Perform address checks
			checkAddress(TRUE);
		
			//Perform postal code checks
			checkZip(TRUE);
		}
				
		if ($errorsPresent === "YES") {
			$_SESSION['errorsPresent'] = TRUE;
			
			//Save previously entered values:
			$_SESSION['type'] = $_POST['usertype'];
			$_SESSION['uname'] = $_POST['username'];
			$_SESSION['nationality'] = $_POST['nationality'];
			$_SESSION['NRIC'] = $_POST['NRIC'];
			$_SESSION['fname'] = $_POST['firstname'];
			$_SESSION['lname'] = $_POST['lastname'];
			$_SESSION['ethnic'] = $_POST['ethnic'];
			$_SESSION['gender'] = $_POST['gender'];
			$_SESSION['btype'] = $_POST['bloodtype'];
			$_SESSION['allergy'] = $_POST['allergy'];
			$_SESSION['dob'] = $_POST['dob'];
			$_SESSION['c1'] = $_POST['contact1'];
			$_SESSION['c2'] = $_POST['contact2'];
			$_SESSION['c3'] = $_POST['contact3'];
			$_SESSION['a1'] = $_POST['address1'];
			$_SESSION['a2'] = $_POST['address2'];
			$_SESSION['a3'] = $_POST['address3'];
			$_SESSION['z1'] = $_POST['zipcode1'];
			$_SESSION['z2'] = $_POST['zipcode2'];
			$_SESSION['z3'] = $_POST['zipcode3'];
			
			header("location: console.php?navi=add");
			exit();
		} else {
			//unset all user input values.
			unset($_SESSION['type']);
			unset($_SESSION['uname']);
			unset($_SESSION['nationality']);
			unset($_SESSION['NRIC']);
			unset($_SESSION['fname']);
			unset($_SESSION['lname']);
			unset($_SESSION['ethnic']);
			unset($_SESSION['gender']);
			unset($_SESSION['btype']);
			unset($_SESSION['allergy']);
			unset($_SESSION['dob']);
			unset($_SESSION['c1']);
			unset($_SESSION['c2']);
			unset($_SESSION['c3']);
			unset($_SESSION['a1']);
			unset($_SESSION['a2']);
			unset($_SESSION['a3']);
			unset($_SESSION['z1']);
			unset($_SESSION['z2']);
			unset($_SESSION['z3']);
			unset($_SESSION['firstrun']);
				
			//Separate salt from password, perform SHA256 on bcrypt hash too.
			$hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);
			$salt = substr($hashedPassword, 0, 29); //first 29 characters of bcrypt hash is the salt.
			$hashedPassword = hash('SHA256', $hashedPassword); //finally, SHA256 the bcrypt string.
			
			//Handle Drug Allergy
			$drugAllergy = FALSE;
			if ($_POST['allergy'] === "Yes" ) {
				$drugAllergy = TRUE;
			}
			
			//Set up array of phone numbers (Handle empty fields too)
			$phoneNumbers = array($_POST['contact1']);
			
			if (!empty(trim($_POST['contact2']))) {
				array_push($phoneNumbers, $_POST['contact2']);
			} else {
				array_push($phoneNumbers, NULL);
			}
			
			if (!empty(trim($_POST['contact3']))) {
				array_push($phoneNumbers, $_POST['contact3']);
			} else {
				array_push($phoneNumbers, NULL);
			}
			
			//Set up array of addresses (Handle empty fields too)
			$addresses = array($_POST['address1']);
			
			if (!empty(trim($_POST['address2']))) {
				array_push($addresses, $_POST['address2']);
			} else {
				array_push($addresses, NULL);
			}
			
			if (!empty(trim($_POST['address3']))) {
				array_push($addresses, $_POST['address3']);
			} else {
				array_push($addresses, NULL);
			}
			
			//Set up array of zip codes (Handle empty fields too)
			$zipcodes = array(intval($_POST['zipcode1']));
			
			if (!empty(trim($_POST['zipcode2']))) {
				array_push($zipcodes, intval($_POST['zipcode2']));
			} else {
				array_push($zipcodes, 0);
			}
			
			if (!empty(trim($_POST['zipcode3']))) {
				array_push($zipcodes, intval($_POST['zipcode3']));
			} else {
				array_push($zipcodes, 0);
			}
			
			//Check therapist value. 1 = TRUE, 0 = FALSE.
			$isTherapist = 0;
			
			if ($_POST['usertype'] === "Therapist" ) {
				$isTherapist = 1;
			}
			
			//POST METHOD 
			$addToDB = array (
				"username"		=> $_POST['username'],
				"password"		=> $hashedPassword,
				"salt" 			=> $salt,
				"firstName"		=> $_POST['firstname'],
				"lastName"		=> $_POST['lastname'],
				"ethnicity"		=> $_POST['ethnic'],
				"nationality"	=> $_POST['nationality'],
				"nric"			=> $_POST['NRIC'],
				"dob"			=> $_POST['dob'],
				"sex"			=> $_POST['gender'],
				"phone"			=> $phoneNumbers,
				"address"		=> $addresses,
				"zipcode"		=> $zipcodes,
				"qualify"		=> $isTherapist,		//this is NOT a string value.
				"bloodtype"		=> $_POST['bloodtype'],
				"drugAllergy"	=> $drugAllergy,
				"secret"		=> "someSecretLUL",		//Stub. Will update this.
				"nfcid"			=> NULL					//Stub(?)
			);
						
			$addToDB_json = json_encode($addToDB);
						
			//Establish connection to DB server and get result.
			$connectionAdd = @ssl::post_content(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/create', 
										$addToDB_json, array('Content-Type: application/json', 'Content-Length: ' . strlen($addToDB_json)));
			
			if ($connectionAdd === FALSE) {
				failedDatabaseConnection('add');
			}
			
			$decodeAdd = json_decode($connectionAdd);
			
			//Result Handling
			$_SESSION['addUserSuccess'] = FALSE;
			$_SESSION['generateAddStatus'] = TRUE;			
			
			if ($decodeAdd->result == 1) {
				$_SESSION['addUserSuccess'] = TRUE;
			}		
			header("location: console.php?navi=add");
			exit();			
		}
		exit();
	}
	
	//This line should not be reached unless form tampering is detected.
	//TODO: Log Transaction.....
	$_SESSION['naviError'] = TRUE;
	header("location: console.php");
	exit();	
?>