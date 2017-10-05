<?php
	//add.php: add functionality of management console. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../index.php");
		exit();
	}
?>

<h1>Add User</h1>
<?php
/*
	This code block generates the error table to list the error messages.
*/
if (isset($_SESSION['errorsPresent']) && $_SESSION['errorsPresent']) {
	echo '
	<div class="error">
		<table>
			<tr><td><img src="img/error.png" height="40" width="40"></td><td>Fix The Following Fields:</td></tr>
		</table>
		<table>'."\n";
			// Display Approrpiate errors as dictated by the session variables. Values set at validate.php
			if (isset($_SESSION['invalidType']) && $_SESSION['invalidType']) {
				echo "\t\t\t" . '<tr><td><b>Patient or Therapist:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
				unset($_SESSION['invalidType']);
			}
			
			if (isset($_SESSION['usernameExists']) && $_SESSION['usernameExists']) {
				echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Username already exists.<br/></td></tr>' . "\n";
				unset($_SESSION['usernameExists']);
			}
			
			if (isset($_SESSION['usernameErr']) && $_SESSION['usernameErr']) {
				echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Only alphanumeric characters allowed.<br/></td></tr>' . "\n";
				unset($_SESSION['usernameErr']);
			}			
			
			if (isset($_SESSION['pwLengthErr']) && $_SESSION['pwLengthErr']) {
				echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Minimum password length must be at least 8.<br/></td></tr>' . "\n";
				unset($_SESSION['pwLengthErr']);
			}
			
			if (isset($_SESSION['pwDiffErr']) && $_SESSION['pwDiffErr']) {
				echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Password fields do not match.<br/></td></tr>' . "\n";
				unset($_SESSION['pwDiffErr']);
			}
			
			//Stub validation. If need be then will implement.
			if (isset($_SESSION['nricExists']) && $_SESSION['nricExists']) {
				echo "\t\t\t" . '<tr><td><b>NRIC/FIN:</b>&emsp;NRIC/FIN number already exists.<br/></td></tr>' . "\n";
				unset($_SESSION['nricExists']);
			}
			
			if (isset($_SESSION['nricInvalid']) && $_SESSION['nricInvalid']) {
				echo "\t\t\t" . '<tr><td><b>NRIC/FIN:</b>&emsp;Invalid value entered. Please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['nricInvalid']);
			}
			
			if (isset($_SESSION['firstNameErr']) && $_SESSION['firstNameErr']) {
				echo "\t\t\t" . '<tr><td><b>First Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['firstNameErr']);
			}
			
			if (isset($_SESSION['lastNameErr']) && $_SESSION['lastNameErr']) {
				echo "\t\t\t" . '<tr><td><b>Last Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['lastNameErr']);
			}
			
			if (isset($_SESSION['invalidGender']) && $_SESSION['invalidGender']) {
				echo "\t\t\t" . '<tr><td><b>Gender:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
				unset($_SESSION['invalidGender']);
			}
			
			if (isset($_SESSION['invalidBlood']) && $_SESSION['invalidBlood']) {
				echo "\t\t\t" . '<tr><td><b>Blood Type:</b>&emsp;Form tampering detected.<br/></td></tr>' . "\n";
				unset($_SESSION['invalidBlood']);
			}
			
			if (isset($_SESSION['dobErr']) && $_SESSION['dobErr']) {
				echo "\t\t\t" . '<tr><td><b>Date of Birth:</b>&emsp;Invalid birthdate, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['dobErr']);
			}
			
			if (isset($_SESSION['contact1Err']) && $_SESSION['contact1Err']) {
				echo "\t\t\t" . '<tr><td><b>Main Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['contact1Err']);
			}
			
			if (isset($_SESSION['contact2Err']) && $_SESSION['contact2Err']) {
				echo "\t\t\t" . '<tr><td><b>Second Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['contact2Err']);
			}
			
			if (isset($_SESSION['contact3Err']) && $_SESSION['contact3Err']) {
				echo "\t\t\t" . '<tr><td><b>Third Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['contact3Err']);
			}
			
			if (isset($_SESSION['addr1Err']) && $_SESSION['addr1Err']) {
				echo "\t\t\t" . '<tr><td><b>Main Address:</b>&emsp;Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['addr1Err']);
			}
			
			if (isset($_SESSION['addr2Err']) && $_SESSION['addr2Err']) {
				echo "\t\t\t" . '<tr><td><b>Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['addr2Err']);
			}
			
			if (isset($_SESSION['addr3Err']) && $_SESSION['addr3Err']) {
				echo "\t\t\t" . '<tr><td><b>Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['addr3Err']);
			}
			
			if (isset($_SESSION['zip1Err']) && $_SESSION['zip1Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 1:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['zip1Err']);
			}
			
			if (isset($_SESSION['zip1Err']) && $_SESSION['zip1Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 1:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['zip1Err']);
			}
			
			if (isset($_SESSION['zip2Err']) && $_SESSION['zip2Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['zip2Err']);
			}
			
			if (isset($_SESSION['zip3Err']) && $_SESSION['zip3Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>' . "\n";
				unset($_SESSION['zip3Err']);
			}
			
			if (isset($_SESSION['emptyField']) && $_SESSION['emptyField']) {
				echo "\t\t\t" . '<tr><td><b>Note:</b>&emsp;One or more required fields are empty. Ensure that they are filled up.<br/></td></tr>' . "\n";
				unset($_SESSION['emptyField']);
			}
			
	echo "\n\t\t" . '</table>
	</div>' . "\n";
	unset($_SESSION['errorsPresent']);
}
?>

<?php
	// This code block generates the result of the add to db functionality.
if (isset($_SESSION['generateAddStatus']) && $_SESSION['generateAddStatus']) {
	echo '
	<div class="error">
		<table>';
		if ($_SESSION['addUserSuccess']) {
			echo '<tr><td><img src="img/tick.png" height="40" width="40"></td><td>User has been successfully added.</td></tr>';
		} else {
			echo '<tr><td><img src="img/cross.png" height="40" width="40"></td><td>ERROR: Failed to add user.</td></tr>';
		}		
		echo '</table>';
	echo '
	</div>' . "\n";
	unset($_SESSION['addUserSuccess']);
	unset($_SESSION['generateAddStatus']);
}
?>

<?php
	// This code block initialises the variables for user input fields
	
	if (!isset($_SESSION['firstrun'])) {
		$_SESSION['type'] = '';
		$_SESSION['uname'] = '';
		$_SESSION['NRIC'] = '';
		$_SESSION['fname'] = '';
		$_SESSION['lname'] = '';
		$_SESSION['gender'] = '';
		$_SESSION['btype'] = '';
		$_SESSION['dob'] = '';
		$_SESSION['c1'] = '';
		$_SESSION['c2'] = '';
		$_SESSION['c3'] = '';
		$_SESSION['a1'] = '';
		$_SESSION['a2'] = '';
		$_SESSION['a3'] = '';
		$_SESSION['z1'] = '';
		$_SESSION['z2'] = '';
		$_SESSION['z3'] = '';
		$_SESSION['firstrun'] = FALSE;
	}
?>
<h3>Enter the relevant details into the fields, click "Submit" to continue:</h3>
<h4 class="required">All fields are required except alternate contact numbers and addresses.</h4>
<div class="addUser">
	<form class="container" method="post" action="validate.php">
		<div class="left">
			<h2><br/>Stage 1: Patient or Therapist<span class="required">*</span></h2>
			<input type="radio" name="usertype" value="Patient" <?php if ($_SESSION['type'] === "Patient") echo "checked";?> required>Patient&emsp;&emsp;&emsp;
			<input type="radio" name="usertype" value="Therapist" <?php if ($_SESSION['type'] === "Therapist") echo "checked";?> required>Therapist
			<h2>Stage 2: Account Details</h2>
			<table>
				<tr>
					<td>Username:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="username" placeholder="Alphanumeric Only" <?php echo 'value="'.$_SESSION['uname'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Password:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="password" name="password" placeholder="Minimum 8 Characters" required>
					</td>
				</tr>
				<tr>
					<td>Confirm Password:<span class="required">*</span>&emsp;</td>
					<td><input type="password" name="cfmPassword" required></td>
				</tr>
			</table>
			<h2>Stage 3: User Information</h2>
			<table>
				<tr>
					<td>NRIC/FIN:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="NRIC" placeholder="S0000000I" size="10" maxlength="9"<?php echo 'value="'.$_SESSION['NRIC'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>First Name:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="firstname" <?php echo 'value="'.$_SESSION['fname'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Last Name:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="lastname" <?php echo 'value="'.$_SESSION['lname'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Gender:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="radio" name="gender" value="M" <?php if ($_SESSION['gender'] === "M") echo "checked";?> required>Male&emsp;&emsp;&emsp;
						<input type="radio" name="gender" value="F" <?php if ($_SESSION['gender'] === "F") echo "checked";?> required>Female
					</td>
				</tr>
				<tr>
					<td>Blood Type:<span class="required">*</span>&emsp;</td>
					<td>
						<select name="bloodtype" required>
						<?php
							$bTypeArr = array("O+", "O-", "A+", "A-", "B+", "B-", "AB+", "AB-");
							$bType = "";
							echo "\t";
							
							if (isset($_SESSION['btype']) && !(empty($_SESSION['btype']))) {
								$bType = $_SESSION['btype'];
								echo '<option value="">Select Blood Type</option>' . "\n";
							} else {
								echo '<option value="" selected>Select Blood Type</option>' . "\n";
							}
							
							foreach ($bTypeArr as $value) {
								echo "\t\t\t\t\t\t\t" . '<option value="' . $value . '"';
								if ($value === $bType) {
									echo ' selected';
								}
								echo '>' . $value . '</option>' . "\n";
							}
						?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<div class="left">
			<table>
				<tr>
					<td colspan="2">
						Warning: Firefox does not support input type: date.<br/>As such, enter value as YYYY-MM-DD (Example: 2000-02-25)<br/>Ignore this message if your browser supports date input.
					</td>
				</tr>
				<tr>
					<td>Date of Birth:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="date" name="dob" placeholder="For Firefox: 2000-12-31" <?php echo 'value="'.$_SESSION['dob'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Main Contact Number:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="contact1" placeholder="91234567" size="8" maxlength="8" <?php echo 'value="'.$_SESSION['c1'].'"';?> required>
						</td>
					</tr>
				<tr>
					<td>Second Contact Number:&emsp;</td>
					<td>
						<input type="text" name="contact2" size="8" maxlength="8" <?php echo 'value="'.$_SESSION['c2'].'"';?>>
					</td>
				</tr>
				<tr>
					<td>Third Contact Number:&emsp;</td>
					<td>
						<input type="text" name="contact3" size="8" maxlength="8" <?php echo 'value="'.$_SESSION['c3'].'"';?>>
					</td>
				</tr>
			</table>
			<table>
				<tr>
					<td>Main Address:<span class="required">*</span>&nbsp;</td>
					<td>
						<input type="text" size="35" name="address1" placeholder="Block 666 Underground Road #B18-6666" <?php echo 'value="'.$_SESSION['a1'].'"';?> required></td><td>Zipcode:<span class="required">*</span>&nbsp;
					</td>
					<td>
						<input type="text" size="6" name="zipcode1" maxlength="6" placeholder="666666" <?php echo 'value="'.$_SESSION['z1'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Addr 2:&nbsp;</td>
					<td>
						<input type="text" size="35" name="address2" <?php echo 'value="'.$_SESSION['a2'].'"';?>>
					</td>
					<td>Zipcode:&nbsp;</td>
					<td>
						<input type="text" size="6" name="zipcode2" maxlength="6" <?php echo 'value="'.$_SESSION['z2'].'"';?>>
					</td>
				</tr>
				<tr>
					<td>Addr 3:&nbsp;</td>
					<td>
						<input type="text" size="35" name="address3" <?php echo 'value="'.$_SESSION['a3'].'"';?>>
					</td>
					<td>Zipcode:&nbsp;</td>
					<td>
						<input type="text" size="6" name="zipcode3" maxlength="6" <?php echo 'value="'.$_SESSION['z3'].'"';?>>
					</td>
				</tr>
			</table>
			<table>
				<tr><td colspan="2"><input type="Submit" value="Submit"></td></tr>
			</table>
		</div>
		<input type="hidden" name="action" value="add">
	</form>
</div>
<div>
	<h2>Batch Adding:</h2>
	This section is a stub. It is intended for administrators to batch add by uploading a file. Exact implementation details are not confirmed.
	<br/><br/>
	Upload file here: Stub..
</div>