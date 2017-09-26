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
			if (isset($_SESSION['usernameExists']) && $_SESSION['userNameExists']) {
				echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Username exists.<br/></td></tr>';
				unset($_SESSION['usernameExists']);
			}
			
			if (isset($_SESSION['usernameErr']) && $_SESSION['usernameErr']) {
				echo "\t\t\t" . '<tr><td><b>Username:</b>&emsp;Only alphanumeric characters allowed.<br/></td></tr>';
				unset($_SESSION['usernameErr']);
			}			
			
			if (isset($_SESSION['pwLengthErr']) && $_SESSION['pwLengthErr']) {
				echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Minimum password length must be at least 8.<br/></td></tr>';
				unset($_SESSION['pwLengthErr']);
			}
			
			if (isset($_SESSION['pwDiffErr']) && $_SESSION['pwDiffErr']) {
				echo "\t\t\t" . '<tr><td><b>Password:</b>&emsp;Password fields do not match.<br/></td></tr>';
				unset($_SESSION['pwDiffErr']);
			}
			
			if (isset($_SESSION['firstNameErr']) && $_SESSION['firstNameErr']) {
				echo "\t\t\t" . '<tr><td><b>First Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['firstNameErr']);
			}
			
			if (isset($_SESSION['lastNameErr']) && $_SESSION['lastNameErr']) {
				echo "\t\t\t" . '<tr><td><b>Last Name:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['lastNameErr']);
			}
			
			if (isset($_SESSION['dobErr']) && $_SESSION['dobErr']) {
				echo "\t\t\t" . '<tr><td><b>Date of Birth:</b>&emsp;Invalid birthdate, please re-enter.<br/></td></tr>';
				unset($_SESSION['dobErr']);
			}
			
			if (isset($_SESSION['contact1Err']) && $_SESSION['contact1Err']) {
				echo "\t\t\t" . '<tr><td><b>Main Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['contact1Err']);
			}
			
			if (isset($_SESSION['contact2Err']) && $_SESSION['contact2Err']) {
				echo "\t\t\t" . '<tr><td><b>Second Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['contact2Err']);
			}
			
			if (isset($_SESSION['contact3Err']) && $_SESSION['contact3Err']) {
				echo "\t\t\t" . '<tr><td><b>Third Contact Number:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['contact3Err']);
			}
			
			if (isset($_SESSION['addr1Err']) && $_SESSION['addr1Err']) {
				echo "\t\t\t" . '<tr><td><b>Main Address:</b>&emsp;Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['addr1Err']);
			}
			
			if (isset($_SESSION['addr2Err']) && $_SESSION['addr2Err']) {
				echo "\t\t\t" . '<tr><td><b>Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['addr2Err']);
			}
			
			if (isset($_SESSION['addr3Err']) && $_SESSION['addr3Err']) {
				echo "\t\t\t" . '<tr><td><b>Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['addr3Err']);
			}
			
			if (isset($_SESSION['zip1Err']) && $_SESSION['zip1Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 1:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['zip1Err']);
			}
			
			if (isset($_SESSION['zip1Err']) && $_SESSION['zip1Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 1:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['zip1Err']);
			}
			
			if (isset($_SESSION['zip2Err']) && $_SESSION['zip2Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 2:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['zip2Err']);
			}
			
			if (isset($_SESSION['zip3Err']) && $_SESSION['zip3Err']) {
				echo "\t\t\t" . '<tr><td><b>Zipcode for Address 3:</b>&emsp;Empty or Invalid, please re-enter.<br/></td></tr>';
				unset($_SESSION['zip3Err']);
			}
			
			if (isset($_SESSION['emptyField']) && $_SESSION['emptyField']) {
				echo "\t\t\t" . '<tr><td><b>Note:</b>&emsp;One or more required fields are empty. Ensure that they are filled up.<br/></td></tr>';
				unset($_SESSION['emptyField']);
			}
			
	echo "\n\t\t" . '</table>
	</div>' . "\n";
	unset($_SESSION['errorsPresent']);
}
?>

<?php
	// This code block initialises the variables for user input fields
	
	if (!isset($_SESSION['firstrun'])) {
		$_SESSION['type'] = '';
		$_SESSION['uname'] = '';
		$_SESSION['fname'] = '';
		$_SESSION['lname'] = '';
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
<h4>All fields are required except alternate contact numbers and addresses.</h4>
<div class="addUser">
	<form class="container" method="post" action="validate.php">
		<div class="left">
			<h2>Stage 1: Patient or Therapist</h2>
			<input type="radio" name="usertype" value="Patient" <?php if ($_SESSION['type'] === "Patient") echo "checked";?> required>Patient&emsp;&emsp;&emsp;
			<input type="radio" name="usertype" value="Therapist" <?php if ($_SESSION['type'] === "Therapist") echo "checked";?> >Therapist
			<h2>Stage 2: Account Details</h2>
			<table>
				<tr>
					<td>Username:&emsp;</td>
					<td>
						<input type="text" name="username" placeholder="Alphanumeric Only" <?php echo 'value="'.$_SESSION['uname'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Password:&emsp;</td>
					<td>
						<input type="password" name="password" placeholder="Minimum 8 Characters" required>
					</td>
				</tr>
				<tr>
					<td>Confirm Password:&emsp;</td>
					<td><input type="password" name="cfmPassword" required></td>
				</tr>
			</table>
			<h2>Stage 3: User Information</h2>
			<table>
				<tr>
					<td>First Name:&emsp;</td>
					<td>
						<input type="text" name="firstname" <?php echo 'value="'.$_SESSION['fname'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Last Name:&emsp;</td>
					<td>
						<input type="text" name="lastname" <?php echo 'value="'.$_SESSION['lname'].'"';?> required>
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
					<td>Date of Birth:&emsp;</td>
					<td>
						<input type="date" name="dob" placeholder="For Firefox: 2000-12-31" <?php echo 'value="'.$_SESSION['dob'].'"';?> required>
					</td>
				</tr>
				<tr>
					<td>Main Contact Number:&emsp;</td>
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
					<td>Main Address:&nbsp;</td>
					<td>
						<input type="text" size="35" name="address1" placeholder="Block 666 Underground Road #B18-6666" <?php echo 'value="'.$_SESSION['a1'].'"';?> required></td><td>Zipcode:&nbsp;
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