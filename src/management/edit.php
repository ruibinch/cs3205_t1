<h1>Edit User</h1>
<h3>This page is a stub. Intention is to search patient by username / id.</h3>
<h3>Meanwhile, will use it to test validate.php redirect functionality.</h3>

<form action="validate.php" method="post">
	Joker: <input type="text" name="joker" required>
	<br/><br/>
	<input type="submit">
	<input type="hidden" name="action" value="edit">
</form>

<br/><br/>

<?php
	if (isset($_SESSION['editfield'])) {
		echo "You have entered " . $_SESSION['editfield'] . " previously.";
		unset($_SESSION['editfield']);
	}
?>