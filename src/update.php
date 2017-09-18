<?php
	$user = "User";
  $first_name = "First Name";
  $last_name = "Last Name";
?>

<html>
<meta charset="utf-8">

  <head>
    <title>Edit your profile</title>
    <link href="css/main.css" rel="stylesheet">
  </head>

  <body>
  	<?php include 'sidebar.php' ?>
	<div class="shifted">
    <h1>Edit your profile</h1>
    <hr style="margin-top:-15px">
    <form class="form-profile" name="form-profile" method="post" action="main.php">
      Username: <input name="input-username" type="text" placeholder="<?php echo $user ?>"><br>
      Password: <input name="input-password" type="password" placeholder="Password"><br>
      First Name: <input name="input-firstname" type="text" placeholder="<?php echo $first_name ?>"><br>
      Last Name: <input name="input-lastname" type="text" placeholder="<?php echo $last_name ?>"><br>
      Date of Birth: <input type="date" name="input-bday"><br>
      Phone: <input type="text" name="input-phone"><br>
      Address: <input type="text" name="input-address"><br>
      Zipcode: <input type="text" name="input-zipcode"><br>

      <input type="submit" id="btn-login" name="login" class="btn-login" value="Save">
    </form>
	</div>
  </body>


</html>