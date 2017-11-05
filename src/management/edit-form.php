<?php
	//edit-form.php: step 2 of editing user information. Generates the stored values in database.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"]);
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
?>

<?php
	//Valid uid set as session variable previously. Will now retreieve current user info.
	$connection = file_get_contents(parse_ini_file($_SERVER['DOCUMENT_ROOT']."/../misc.ini")['server4'].'api/team1/user/uid/' . $_SESSION['editUserID']);
	
	//IF database connection failed
	if ($connection === FALSE) {
		$_SESSION['generateEditStatus'] = TRUE;
		$_SESSION['editUserSuccess'] = FALSE;
		echo "<script>window.location = 'console.php?navi=edit'</script>";
		exit();
	}	
	
	$result = json_decode($connection);
	
	//handle zipcodes as they are stored as integer:	
	if ($result->zipcode[1] == 0) {
		$z2 = "";
	} else {
		$z2 = $result->zipcode[1];
	}
	
	if ($result->zipcode[2] == 0) {
		$z3 = "";
	} else {
		$z3 = $result->zipcode[2];
	}
?>

<br/>
<h2>Edit the respective information and submit.</h2>
<h2>To change password, simply type in the new password.</h2>
<h2>Scroll down and click submit to view error messages (if any).</h2>
<h2 class="errorDel">You are now modifying uid: <?php echo $result->uid;?></h2>
<form id="formEdit2" class="container">
	<div class="left">
		<h2><br/>Stage 1: Patient or Therapist<span class="required">*</span></h2>
		<input type="radio" name="usertype" value="Patient" <?php if ($result->qualify == 0) echo "checked";?> required>Patient&emsp;&emsp;&emsp;
		<input type="radio" name="usertype" value="Therapist" <?php if ($result->qualify == 1) echo "checked";?> required>Therapist
		<h2>Stage 2: Account Details</h2>
		<table>
			<tr>
				<td>Username:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="username" placeholder="Alphanumeric Only" <?php echo 'value="'.htmlspecialchars($result->username).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>Password:&emsp;</td>
				<td>
					<input type="password" name="password" placeholder="Minimum 8 Characters">
				</td>
			</tr>
			<tr>
				<td>Confirm Password:&emsp;</td>
				<td><input type="password" name="cfmPassword"></td>
			</tr>
		</table>
		<h2>Stage 3: User information</h2>
		<table>
			<tr>
				<td>Nationality:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="nationality" placeholder="Singaporean" <?php echo 'value="'.htmlspecialchars($result->nationality).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>NRIC/FIN:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="NRIC" placeholder="S0000000I" size="10" maxlength="9"<?php echo 'value="'.htmlspecialchars($result->nric).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>First Name:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="firstname" <?php echo 'value="'.htmlspecialchars($result->firstname).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>Last Name:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="lastname" <?php echo 'value="'.htmlspecialchars($result->lastname).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>Ethnicity:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="ethnic" placeholder="Chinese" <?php echo 'value="'.htmlspecialchars($result->ethnicity).'"';?> required>
					</td>
				</tr>
			<tr>
				<td>Sex:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="radio" name="gender" value="M" <?php if ($result->sex === "M") echo "checked";?> required>Male&emsp;&emsp;&emsp;
					<input type="radio" name="gender" value="F" <?php if ($result->sex === "F") echo "checked";?> required>Female
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
						
						if (isset($result->bloodtype)) {
							$bType = $result->bloodtype;
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
			<tr>
				<td>Drug Allergy:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="radio" name="allergy" value="Yes" <?php if ($result->drugAllergy) echo "checked";?> required>Yes&emsp;&emsp;&emsp;
						<input type="radio" name="allergy" value="No" <?php if (!($result->drugAllergy)) echo "checked";?> required>No
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
					<input type="date" name="dob" placeholder="For Firefox: 2000-12-31" <?php echo 'value="'.htmlspecialchars($result->dob).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>Main Contact Number:<span class="required">*</span>&emsp;</td>
				<td>
					<input type="text" name="contact1" placeholder="91234567" size="8" maxlength="8" <?php echo 'value="'.htmlspecialchars($result->phone[0]).'"';?> required>
					</td>
				</tr>
			<tr>
				<td>Second Contact Number:&emsp;</td>
				<td>
					<input type="text" name="contact2" size="8" maxlength="8" <?php echo 'value="'.htmlspecialchars($result->phone[1]).'"';?>>
				</td>
			</tr>
			<tr>
				<td>Third Contact Number:&emsp;</td>
				<td>
					<input type="text" name="contact3" size="8" maxlength="8" <?php echo 'value="'.htmlspecialchars($result->phone[2]).'"';?>>
				</td>
			</tr>
		</table>
		<table>
			<tr>
				<td>Main Address:<span class="required">*</span>&nbsp;</td>
				<td>
					<input type="text" size="35" name="address1" placeholder="Block 666 Underground Road #B18-6666" <?php echo 'value="'.htmlspecialchars($result->address[0]).'"';?> required></td><td>Zipcode:<span class="required">*</span>&nbsp;
				</td>
				<td>
					<input type="text" size="6" name="zipcode1" maxlength="6" placeholder="666666" <?php echo 'value="'.htmlspecialchars($result->zipcode[0]).'"';?> required>
				</td>
			</tr>
			<tr>
				<td>Addr 2:&nbsp;</td>
				<td>
					<input type="text" size="35" name="address2" <?php echo 'value="'.htmlspecialchars($result->address[1]).'"';?>>
				</td>
				<td>Zipcode:&nbsp;</td>
				<td>
					<input type="text" size="6" name="zipcode2" maxlength="6" <?php echo 'value="'.htmlspecialchars($z2).'"';?>>
				</td>
			</tr>
			<tr>
				<td>Addr 3:&nbsp;</td>
				<td>
					<input type="text" size="35" name="address3" <?php echo 'value="'.htmlspecialchars($result->address[2]).'"';?>>
				</td>
				<td>Zipcode:&nbsp;</td>
				<td>
					<input type="text" size="6" name="zipcode3" maxlength="6" <?php echo 'value="'.htmlspecialchars($z3).'"';?>>
				</td>
			</tr>
		</table>
		<table>
			<tr><td colspan="2"><input type="Submit" value="Submit"></td></tr>
		</table>
	</div>
	<input type="hidden" name="action" value="edit2">
</form>

<div class="contentField" id="loaderDiv2">
	<br/>
	<div class="loader" id="loader"></div>
	<br/>
</div>

<!-- Consider making a separate js file-->
<script>
	$(document).ready(function(){
		$('#formEdit2').on('submit', function(e){
			$("#errorDiv").hide();
			$("#loaderDiv2").show();
			$('html, body').animate({scrollTop:$(document).height()},500);
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/management/validate.php',
				data: $('#formEdit2').serialize(),
				cache: false,
				success: function(result) {
					$("#loaderDiv2").hide();
					$("#errorDiv").show();
					$("#errorDiv").html(result);
					$('html, body').animate({scrollTop:0},500);
				},
				error: function() {
					alert('Warning: Server may be unavailable at this point in time.');
				}
			});
		});
	});
</script>