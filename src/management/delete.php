<?php
	//delete.php: delete functionality of management console. accessible by php include only.

	//Shitty way to prevent direct access.
	debug_backtrace() OR die ("Direct Access Forbidden.");
	
	// TODO: change the dummy key here to the real key
	WebToken::verifyToken($_COOKIE["jwt"], "dummykey");
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $_SERVER["DOCUMENT_ROOT"] . "/index.php");
		exit();
	}
	
	//Set Navigation Session Variable
	$_SESSION['latestAction'] = "DELETE";
?>

<h1>Delete User</h1>
<?php
	if (isset($_SESSION['printThirdArea']) && $_SESSION['printThirdArea']) {
		echo '<h3 class="required">';
		if ($_SESSION['successfulDeletion']) {
			echo 'User Account Deleted. Farewell.';
		} else {
			echo 'Failed to delete user.';
		}
		echo '</h3>' . "\n";
		
		unset($_SESSION['successfulDeletion']);
		unset($_SESSION['printThirdArea']);
	}
?>
<br/>
<div class="contentField">
	<br/>
	<h2>Enter username to begin.</h2>
	<form class="container" method="post" action="/management/validate.php">
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
	<br/>
</div>
<br/><br/>
<?php
	if (isset($_SESSION['printSecondArea']) && $_SESSION['printSecondArea']) {
		echo '<div class="contentField">' . "\n<br/>";		
		if ($_SESSION['validForDeletion']) {
			echo "\t" . '<h3 class="errorDel">CONFIRM USER DELETION: ' . htmlspecialchars($_SESSION['delUserName']) . ' with uid: ' . $_SESSION['delUserID'] . '.</h3>' . "\n";
			echo "\t" . '<form method="post" action="validate.php">' . "\n";
			echo "\t\t" . '<input type="checkbox" name="cfmDelete" required>Yes, I wish to delete the following user.' . "\n<br/><br/>";
			echo "\t\t" . '<input type="Submit" value="Confirm Deletion">' . "\n";			
			echo "\t\t" . '<input type="hidden" name="cfmUserName" value="'. htmlspecialchars($_SESSION['delUserName']) .'">' . "\n";
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