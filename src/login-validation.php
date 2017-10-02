<?php
    // Login validation for healthcare system

    session_start();

    // TODO - update from DB API    
    $username = "team1";
	$password = '$2y$10$NHpcPdotapEtq5t2HR.iV.OVHzV8oCymH9n3Fwhnh0CYP9xGE6oG6';  //plaintext: corgi
	
	if ($_POST['hc-username'] === $username && password_verify($_POST['hc-password'], $password)) {
        $_SESSION['username'] = $username;
        $_SESSION['user_type'] = $_POST['user_type'];
		header("location: main.php");
		exit();
	} else {
        session_destroy();
        header("location: login.php?err=1");
    }

?>