<!DOCTYPE html>

<?php
	/* 
		TODO:
			- JWT Session
			- Connection to DB using restful API
			- Edit / Delete / Transaction / Validate
			- Design (almost done..)
	*/
    session_start();
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
					<h1>Management Console</h1>
					<br/>
					<h3>Logged in as: Test User</h3>
				</div>
				<div class="menu">
					<br/><br/><br/>
					<ul>
						<a href="console.php?navi=add"><li>Add User</li></a>
						<a href="console.php?navi=edit"><li>Edit User</li></a>
						<a href="console.php?navi=delete"><li>Delete User</li></a>
						<a href="console.php?navi=txhistory"><li>Server Transactions</li></a>
						<br/>
						<a href="console.php?navi=logout"><li>Logout</li></a>
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
							echo "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";
							break;
					}
				?>
			</div>
		</div>
	</body>
</html> 