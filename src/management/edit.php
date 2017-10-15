<?php
	//edit.php: edit functionality of management console. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../index.php");
		exit();
	}
	
	//Set Navigation Session Variable
	$_SESSION['latestAction'] = "EDIT";
?>
<h1>Edit User</h1>
<h3>Enter username to begin.</h3>

<div class="contentField">
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
				}
			});
		});
	});
</script>
<?php
	/*
	
	<form action="validate.php" method="post">
		Joker: <input type="name" name="NRIC" required>
		<br/><br/>
		<input type="submit">
		<input type="hidden" name="action" value="edit">
	</form>

<br/><br/>
	
	if (isset($_SESSION['editfield'])) {
		echo "You have entered " . $_SESSION['editfield'] . " previously.\n";
		unset($_SESSION['editfield']);
		
		if (isset($_SESSION['namecheck']) && $_SESSION['namecheck']) {
			echo "Passed.";
		} else {
			echo "Failed.";
		}
	}
	*/
?>