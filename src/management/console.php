<!DOCTYPE html>

<?php
	/* 
		TODO:
			- JWT Session
			- Connection to DB using restful API
			- Edit / Delete / Transaction / Validate
			- Design (almost done..)
	*/
	
	//console.php: management console's main page.
    session_start();
	
	if (!isset($_SESSION['loggedin'])) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: /index.php");
		exit();
	}
	
	if (!isset($_GET['navi'])) {
		$_GET['navi'] = NULL;
	}
	
	/*
		Navigation Session Variable: $_SESSION['latestAction']
		PURPOSE: Reduce the attack surface due to cross-submitting form data from different pages (Example: Add User form data submitted to Edit User's validation section). Only records Add, Edit or Delete User.
		
		There are 4 possible values: "NONE", "ADD", "EDIT", "DELETE"
		
		Note that this still does not prevent multiple instances of same page.
	*/
	$_SESSION['latestAction'] = "NONE";
	
?>

<html>
	<head>
		<title>Skeleton Management Page</title>
		<link rel="stylesheet" type="text/css" href="css/mgmt.css" />
		<script	src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	</head>
	<body>
		<div>
			<nav class="nav">
				<div class="header">
					<h2>Management Console</h2>
					<br/>
					<h3>Welcome, <br/><?php echo $_SESSION['loggedin']?></h3>
				</div>
				<div class="menu">
					<br/><br/><br/>
					<ul>
						<a href="console.php?navi=add"><li<?php if ($_GET['navi'] === "add") echo ' class="selectedNavi"';?>>Add User</li></a>
						<a href="console.php?navi=edit"><li<?php if ($_GET['navi'] === "edit") echo ' class="selectedNavi"';?>>Edit User</li></a>
						<a href="console.php?navi=delete"><li<?php if ($_GET['navi'] === "delete") echo ' class="selectedNavi"';?>>Delete User</li></a>
						<a href="console.php?navi=txhistory"><li<?php if ($_GET['navi'] === "txhistory") echo ' class="selectedNavi"';?>>Server Transactions</li></a>
						<br/>
						<a href="logout.php"><li>Logout</li></a>
					</ul>
				</div>
			</nav>
			<div class="content">
				<?php
					switch ($_GET["navi"] ?? '') {
						case "add":
							include "add.php";
							break;
						case "edit":
							include "edit.php";
							break;
						case "delete":
							include "delete.php";
							break;
						case "txhistory":
							include "txhistory.php";
							break;
						default:
							echo '<h1><---- Select one of the options on the menu bar to continue.</h1>' . "\n";
							echo "\t\t\t\t" . '<h2 class="required">WARNING: To prevent tampering, only the LATEST INSTANCE of {Add, Edit, Delete} User is considered a valid request to the server.</h2>' . "\n";
							if (isset($_SESSION['naviError'])) {
								echo "\t\t\t\t" . '<div class="error">' . "\n\t\t\t\t\t" . '<br/><h1>ERROR: Invalid action detected.<br/>Ensure that you do not use multiple windows/tabs for User Modifiations.</h1><br/>' . "\n\t\t\t\t" . '</div>' . "\n";
								$_SESSION['latestAction'] = "NONE";
								unset($_SESSION['naviError']);
							}
							break;
					}
				?>
			</div>
		</div>
	</body>
</html> 