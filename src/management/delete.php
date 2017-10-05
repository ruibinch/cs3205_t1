<?php
	//delete.php: delete functionality of management console. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: ../index.php");
		exit();
	}
?>

<h1>Delete User</h1>
<?php
	if (isset($_SESSION['printThirdArea']) && $_SESSION['printThirdArea']) {
		echo '<h3 class="required">';
		if ($_SESSION['successfulDeletion']) {
			echo 'User Account Deleted. Farewell.';
		} else {
			echo 'Failed to delete the username.';
		}
		echo '</h3>' . "\n";
		
		unset($_SESSION['successfulDeletion']);
		unset($_SESSION['printThirdArea']);
	}
?>
<h3>Enter username of user.</h3>
<div class="addUser">
	<form class="container" method="post" action="validate.php">
		<div class="left">
			<table>
				<tr>
					<td>Username:<span class="required">*</span>&emsp;</td>
					<td>
						<input type="text" name="username" required>
					</td>
				</tr>
				<tr><td colspan="2"><input type="Submit" value="Submit"></td></tr>
			</table>
		</div>
	<input type="hidden" name="action" value="delete">
	</form>
</div>
<br/><br/>
<?php
	if (isset($_SESSION['printSecondArea']) && $_SESSION['printSecondArea']) {
		echo '<div class="addUser">' . "\n<br/>";		
		if ($_SESSION['validForDeletion']) {
			echo "\t" . '<h3 class="errorDel">CONFIRM USER DELETION: ' . $_SESSION['delUserName'] . '.</h3>' . "\n";
			echo "\t" . '<form method="post" action="validate.php">' . "\n";
			echo "\t\t" . '<input type="checkbox" name="cfmDelete" required>Yes, I wish to delete the following user.' . "\n<br/><br/>";
			echo "\t\t" . '<input type="Submit" value="Confirm Deletion">' . "\n";
			echo "\t\t" . '<input type="hidden" name="action" value="delete2">' . "\n";
			echo "\t" . '</form>' . "\n";
		} else {
			echo "\t" . '<h3 class="errorDel">ERROR: username not found.</h3>' . "\n";
		}
		unset($_SESSION['delUserName']);
		unset($_SESSION['validForDeletion']);
		unset($_SESSION['printSecondArea']);
		echo "\t" . '<br/>';
		echo '</div>';
	}
?>