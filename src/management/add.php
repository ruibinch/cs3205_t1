<h1>Add User</h1>
<h3>Enter the relevant details into the fields, click "Submit" to continue:</h3>
<div class="addUser">
	<form class="container">
		<div class="left">
			<h2>Stage 1: Patient or Therapist</h2>
			<input type="radio" name="usertype" value="Patient" required>Patient &emsp; &emsp; &emsp;
			<input type="radio" name="usertype" value="Therapist">Therapist
			<br/><br/><br/>
			<h2>Stage 2: Enter Account Details</h2>
			<table>
				<tr><td>Username:&emsp;</td><td><input type="text" name="username" required></td></tr>
				<tr><td>Password:&emsp;</td><td><input type="password" name="password" required></td></tr>
				<tr><td>Confirm Password:&emsp;</td><td><input type="password" name="cfmPassword" required></td></tr>
			</table>
		</div>
		<div class="left">
			<h2>Stage 3: Enter User Information</h2>
			<table>
				<tr><td>First Name:&emsp;</td><td><input type="text" name="firstname" required></td></tr>
				<tr><td>Last Name:&emsp;</td><td><input type="text" name="lastname" required></td></tr>
				<tr><td>Date of Birth:&emsp;</td><td><input type="date" name="dob" required></td></tr>
				<tr><td colspan="2">Warning: Firefox does not support input type: date.<br/>Enter value as YYYY-MM-DD (Example: 2000-12-25)</td></tr>
				<tr><td>Contact Number:&emsp;</td><td><input type="text" name="contact" required></td></tr>
				<tr><td>Second Contact Number:&emsp;</td><td><input type="text" name="contact_backup" ></td></tr>
				<tr><td>Third Contact Number:&emsp;</td><td><input type="text" name="contact_backup2" ></td></tr>
			</table>
			<table>
				<tr><td>Address 1:&emsp;</td><td><input type="text" size="50" name="address" required	></td></tr>
			</table>
			<table>
				<tr><td>Postal Code:&emsp;</td><td><input type="text" name="zipcode" required></td></tr>
				<tr><td colspan="2"><input type="Submit" value="Submit"></td></tr>
			</table>
		</div>
	</form>
</div>
<div>
	<h2>Batch Adding:</h2>
	This section is a stub. It is intended for administrators to batch add by uploading a file. Exact implementation details are not confirmed.
	<br/><br/>
	Upload file here: Stub..
</div>