<?php
	if ($_SERVER['REQUEST_METHOD'] === 'GET') {
		echo "Go home, you are drunk.";
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === "edit") {
		session_start();
		$_SESSION['editfield'] = $_POST['joker'];
		echo "test: " . $_SESSION['editfield'];
		header("location: console.php?navi=edit"); /* Redirect browser */
		exit();
	}
?>