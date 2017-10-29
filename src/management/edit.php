<?php
	//edit.php: edit functionality of management console. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
	
	//Remove editUserID session if "Edit User" page is clicked on again.
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		unset($_SESSION['editUserID']);
	}
	
	//Set Navigation Session Variable
	$_SESSION['latestAction'] = "EDIT";
?>
<h1>Edit User</h1>

<?php
	// This code block generates the result of the edit to db functionality.
if (isset($_SESSION['generateEditStatus']) && $_SESSION['generateEditStatus']) {
	echo '
	<div class="error" id="statusWindow">
		<table>';
		if ($_SESSION['editUserSuccess']) {
			echo '<tr><td><img src="img/tick.png" height="40" width="40"></td><td>User profile has been successfully modified.</td></tr>';
		} else {
			echo '<tr><td><img src="img/cross.png" height="40" width="40"></td><td>ERROR: Failed to edit user.</td></tr>';
		}		
		echo '</table>';
	echo '
	</div>' . "\n";
	unset($_SESSION['editUserSuccess']);
	unset($_SESSION['generateEditStatus']);
}
?>

<br/>
<div class="contentField">
<br/>
<h2>Enter username to begin.</h2>
	<form id="formEdit" class="container">
		<div class="left">
			<table>
				<tr>
					<td>Username:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="editUserName" required>
					</td>
				</tr>
				<tr><td colspan="2"><input type="Submit" value="Submit"></td></tr>
			</table>
		</div>
		<input type="hidden" name="action" value="edit1">
	</form>	
	<br/>
</div>

<br/><br/>

<div class="contentField" id="resultDiv">
</div>

<div class="contentField" id="loaderDiv">
	<br/>
	<div class="loader" id="loader"></div>
	<br/>
</div>

<!-- Consider making a separate js file-->
<script>
	$(document).ready(function(){
		$('#formEdit').on('submit', function(e){
			$("#resultDiv").hide();
			$("#statusWindow").hide();
			$("#loaderDiv").show();
			e.preventDefault();
			$.ajax({
				type: 'POST',
				url: '/management/validate.php',
				data: $('#formEdit').serialize(),
				cache: false,
				success: function(result) {
					$("#loaderDiv").hide();
					$("#resultDiv").show();
					$("#resultDiv").html(result);
				},
				error: function() {
					alert('Warning: Server may be unavailable at this point in time.');
				}
			});
		});
	});
</script>