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
		header("Location: ../index.php");
		exit();
	}
?>

<html>
	<head>
		<title>Skeleton Management Page</title>
		<link rel="stylesheet" type="text/css" href="css/mgmt.css" />
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
						<a href="console.php?navi=add"><li>Add User</li></a>
						<a href="console.php?navi=edit"><li>Edit User</li></a>
						<a href="console.php?navi=delete"><li>Delete User</li></a>
						<a href="console.php?navi=txhistory"><li>Server Transactions</li></a>
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
							echo '<h1><---- Select one of the options on the menu bar to continue.</h1>';
							break;
					}
				?>
			</div>
		</div>
	</body>
</html> 