<?php
	//validate.php: Checks submitted form values.
	//TODO: Clarify with team the maxlength of each field.
	
	session_start();	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../index.php");
		exit();
	}
	
	//GLOBAL VARIABLES DECLARATION:
	$errorsPresent = "NO"; //track whether are there any validation errors.
	
?>
<?php
	// THIS SECTION DEALS WITH THE VALIDATION CHECKS. IT COMPRISES OF SEVERAL FUNCTIONS.
	
	/*
		checkEmptyFields():
			FUNCTIONALITY: Performs the following check:
				1) Ensure that all the required fields are filled in.
			INPUT: NONE. Use all POST data that are required
			OUTPUT: Adds session variable $_SESSION['emptyField'] = TRUE if it contains other characters.
					Session variable NOT PRESENT if required fields are filled in.
	*/	
	function checkEmptyFields() {
		global $errorsPresent;
		
		if (empty(trim($_POST['usertype'])) OR empty(trim($_POST['username'])) OR empty($_POST['password']) OR empty($_POST['cfmPassword']) OR empty(trim($_POST['firstname'])) OR empty(trim($_POST['lastname'])) OR empty($_POST['bloodtype']) OR empty(trim($_POST['dob'])) OR empty(trim($_POST['contact1'])) OR empty(trim($_POST['address1'])) OR empty(trim($_POST['zipcode1']))) {
			$_SESSION['emptyField'] = TRUE; //failed check
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkUserType():
			FUNCTIONALITY: Perform user type check. Since this field has only 2 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: NONE. Use $_POST['usertype'].
			OUTPUT: Adds session variable $_SESSION['invalidType'] = TRUE if value is neither 'Patient' nor 'Therapist'.
			
	*/	
	
	function checkUserType() {
		global $errorsPresent;
		
		if (!($_POST['usertype'] === "Therapist" || $_POST['usertype'] === "Patient")) {
			$_SESSION['invalidType'] = TRUE; //failed check
			$errorsPresent = "YES"; //triggers the variable to true.
		}
	}
		
	/*
		checkUserName():
			FUNCTIONALITY: Performs the following check:
				1) Whether username already exists in database (NOTE: NOT IMEPLEMETED YET).
				2) Ensure that username only contains alphanumeric characters.
			INPUT: NONE. Use $_POST['username'].
			OUTPUT: Adds session variable $_SESSION['usernameErr'] = TRUE if it contains other characters.
					Session variable NOT PRESENT if username only contains alphanumeric characters.
	*/	
	function checkUserName() {
		global $errorsPresent;
		
		// TO IMPLEMENT USERNAME EXISTS, SKIP THE NEXT CHECK IF ALREADY EXISTS!
		if (preg_match("/[^A-Za-z0-9]/", $_POST['username'])) {
			$_SESSION['usernameErr'] = TRUE;
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkPassword():
			FUNCTIONALITY: Performs the following check:
				1) Password length is at least 8 characters.
				2) Password is entered correctly (check with 'confirm password' field).
			INPUT: NONE. Use $_POST['password'] AND $_POST['cfmPassword'].
			OUTPUT: Adds session variable $_SESSION['pwLengthErr'] = TRUE if pw length < 8 char.
					If that passes, adds session variable $_SESSION['pwDiffErr'] = TRUE if pw does not match.
					Session variables NOT PRESENT if it passes the checks.
	*/
	function checkPassword() {
		global $errorsPresent;
		
		if (strlen($_POST['password']) < 8) {
			$_SESSION['pwLengthErr'] = TRUE;
			$errorsPresent = "YES";
			return;
		}
		
		if ($_POST['password'] != $_POST['cfmPassword']) {
			$_SESSION['pwDiffErr'] = TRUE;
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkFirstAndLastName():
			FUNCTIONALITY: Performs the following check:
				1) First and Last Name String complies with the following regex: /^[\p{L}\s'.-]+$/
				For explanation of regex, see https://regex101.com/r/rT1exI/1
			INPUT: NONE. Use $_POST['firstname'] AND $_POST['lastname'].
			OUTPUT: Adds session variable $_SESSION['[first(AND|OR)last]NameErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
	*/
	function checkFirstAndLastName() {
		global $errorsPresent;
		
		if (!preg_match("/^[\p{L}\s'.-]+$/", $_POST['firstname'])) {
			$_SESSION['firstNameErr'] = TRUE;
			$errorsPresent = "YES";
		}
		
		if (!preg_match("/^[\p{L}\s'.-]+$/", $_POST['lastname'])) {
			$_SESSION['lastNameErr'] = TRUE;
			$errorsPresent = "YES";
		} 
	}
	
	/*
		checkBloodType():
			FUNCTIONALITY: Performs blood type check. Since this field has only 8 options, the posibility of selecting something invalid is NIL unless somebody purposely manipulated the form data upon submission. In this case, this action **MAY BE RECORDED** (Need to implement).
			INPUT: NONE. Use $_POST['bloodtype'].
			OUTPUT: Adds session variable $_SESSION['invalidBlood'] = TRUE if value is not in the dropdown list.
	*/
	function checkBloodType() {
		global $errorsPresent;
		
		$bTypeArr = array("O+", "O-", "A+", "A-", "B+", "B-", "AB+", "AB-");
		
		if (!in_array($_POST['bloodtype'], $bTypeArr, TRUE)) {
			$_SESSION['invalidBlood'] = TRUE; //failed check
			$errorsPresent = "YES";
		}
	}
	
	/*
		checkDOB():
			FUNCTIONALITY: Performs the following check:
				1) If the date format supplied is valid (yyyy-mm-dd). Modern HTML5 browsers, except firefox, will definitely pass this as it supports type="date".
				
				The regex expression to check (URL: https://regex101.com/r/ChegrJ/1 ): /^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/
				
				Checks for the format, number of days in respective months. However, it does not check whether is it a leap year. Will perform manual check on it.
			INPUT: NONE. Use $_POST['dob'].
			OUTPUT: Add session variable $_SESSION['dobErr'] = TRUE if the check fails. Session variable NOT PRESENT if it passes the checks.
	*/
	function checkDOB() {
		global $errorsPresent;
		
		if (strlen($_POST['dob']) != 10 OR !preg_match("/^\d{4}[\-\/\s]?((((0[13578])|(1[02]))[\-\/\s]?(([0-2][0-9])|(3[01])))|(((0[469])|(11))[\-\/\s]?(([0-2][0-9])|(30)))|(02[\-\/\s]?[0-2][0-9]))$/", $_POST['dob'])) { //perform initial format check
			$_SESSION['dobErr'] = TRUE;
			$errorsPresent = "YES";
		} else { //check cleared. Now to determine state of leap year.
			$year = substr($_POST['dob'], 0, 4);
			$month = substr($_POST['dob'], 5, 2);
			$day = substr($_POST['dob'], 8, 2);
			
			if ($month === "02" && $day === "29") { //if it is feb 29, check for valid year
				if (!((($year % 4) == 0) && ((($year % 100) != 0) || (($year % 400) == 0)))) { //if not valid year
					$_SESSION['dobErr'] = TRUE;
					$errorsPresent = "YES";
				}
			}
		}
	}
	
	/*
		checkContactNumber():
			FUNCTIONALITY: Performs the following check:
				1) Ensures that the value entered is valid for a contact number (8-digit numeric).
				2) If contact2 and contact3 are not empty, check them too.
			INPUT: NONE. Use $_POST['contact1'], $_POST['contact2'] and $_POST['contact3']
			OUTPUT: Add relevant session variable(s) if check fails (see comments in function for listing). Session variable(s) NOT PRESENT if it passes the checks.
	*/
	function checkContactNumber() {
		global $errorsPresent;
		
		if (isContactNumberInvalid($_POST['contact1']) == TRUE) {
			$_SESSION['contact1Err'] = TRUE; //sets the session variable for contact1's error
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['contact2']) && isContactNumberInvalid($_POST['contact2'])){
			$_SESSION['contact2Err'] = TRUE; //sets the session variable for contact2's error
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['contact3']) && isContactNumberInvalid($_POST['contact3'])) {
			$_SESSION['contact3Err'] = TRUE; //sets the session variable for contact3's error
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
		checkAddress():
			FUNCTIONALITY: Performs the following check:
				1) Ensure that the address string is alphanumeric only, with space character, # and - as exceptions.
				2) The minimum length of string should be at least 6 (arbitary, can adjust when needed).
				3) If address2 and address3 are not empty, check them too.
			INPUT: NONE. Use $_POST['address1'], $_POST['address2'] and $_POST['address3']
			OUTPUT: Add relevant session variable(s) if check fails (see comments in function for listing). Session variable(s) NOT PRESENT if it passes the checks.
	*/
	function checkAddress() {
		global $errorsPresent;
		
		if (isAddressInvalid($_POST['address1']) == TRUE) {
			$_SESSION['addr1Err'] = TRUE; //sets the session variable for address1's error
			$errorsPresent = "YES";
		}
		
		if ((!empty($_POST['address2']) && isAddressInvalid($_POST['address2'])) || (empty($_POST['address2']) && !empty($_POST['zipcode2']))){
			$_SESSION['addr2Err'] = TRUE; //sets the session variable for address2's error
			$errorsPresent = "YES";
		}
		
		if ((!empty($_POST['address3']) && isAddressInvalid($_POST['address3'])) || (empty($_POST['address3']) && !empty($_POST['zipcode3']))){
			$_SESSION['addr3Err'] = TRUE; //sets the session variable for address3's error
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
		checkZip():
			FUNCTIONALITY: Performs the following check:
				1) String consists of all numbers
				2) String is exactly 6 characters in length
				3) If address2 and address3 are not empty, perform checks on zipcode2 and zipcode3 too.
			INPUT: NONE. Use $_POST['zipcode1'], $_POST['zipcode2'] and $_POST['zipcode3']
	*/
	function checkZip() {
		global $errorsPresent;
		
		if (isZipcodeInvalid($_POST['zipcode1']) == TRUE) {
			$_SESSION['zip1Err'] = TRUE; //sets the session variable for zipcode1's error
			$errorsPresent = "YES";
		}
		
		if (!empty($_POST['address2'])) { //if address2 field is not empty, check zipcode2
			if (isZipcodeInvalid($_POST['zipcode2']) == TRUE) {
				$_SESSION['zip2Err'] = TRUE; //sets the session variable for zipcode2's error
				$errorsPresent = "YES";
			}
		}
		
		if (!empty($_POST['address3'])) { //if address3 field is not empty, check zipcode3
			if (isZipcodeInvalid($_POST['zipcode3']) == TRUE) {
				$_SESSION['zip3Err'] = TRUE; //sets the session variable for zipcode3's error
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

<?php	
	// THIS SECTION DEALS WITH THE ACTUAL POST REQUEST THAT ARRIVE

	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		echo "Go home, you are drunk.";
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "edit") {
		/*
		session_start();
		if (preg_match("/[^A-Za-z0-9]/", $_POST['joker'])) {
			$_SESSION['namecheck'] = FALSE; //failed check
		} else {
			$_SESSION['namecheck'] = TRUE; //passed check
		}
		$_SESSION['editfield'] = $_POST['joker'];
		header("location: console.php?navi=edit");
		exit();
		*/
		echo password_hash($_POST['joker'], PASSWORD_BCRYPT);
	}
	
	//IMPLEMENT HTTP_REFERER WITH WORKING SERVER - $_SERVER['HTTP_REFERER'];
	//SECURITY ISSUES: FORM MANIPULATION FROM OTHER PAGE
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "add") {
		
		//Perform empty fields check
		checkEmptyFields();
		
		if ($errorsPresent === "NO") {
			//Perform usertype check
			checkUserType();
			
			//Perform username check
			checkUserName();
		
			//Perform password check
			checkPassword();
		
			//Perform firstname check
			checkFirstAndLastName();
			
			//Perform blood type check
			checkBloodType();
		
			//Perform dob check
			checkDOB();
		
			//Perform contact number checks
			checkContactNumber();
		
			//Perform address checks
			checkAddress();
		
			//Perform postal code checks
			checkZip();
		}
				
		if ($errorsPresent === "YES") {
			$_SESSION['errorsPresent'] = TRUE;
			
			//Save previously entered values:
			$_SESSION['type'] = $_POST['usertype'];
			$_SESSION['uname'] = $_POST['username'];
			$_SESSION['fname'] = $_POST['firstname'];
			$_SESSION['lname'] = $_POST['lastname'];
			$_SESSION['btype'] = $_POST['bloodtype'];
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
			unset($_SESSION['fname']);
			unset($_SESSION['lname']);
			unset($_SESSION['btype']);
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
			
			echo "Nothing Wrong... Ready to add into database. TODO.";
		}
		exit();
	}
?>