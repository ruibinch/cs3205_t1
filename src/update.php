<?php

  session_start();
  if (isset($_SESSION["user_json"])) {
    $user_json = $_SESSION["user_json"];
  }

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
    <form class="profile-form" name="profile-form" method="post" action="main.php">
      <div class="profile-update">Username:<br>
        <input name="input-username" type="text" placeholder="<?php echo $user ?>"
          value="<?php echo (isset($user_json->username) ? $user_json->username : "" )?>"><br>
      </div>
      <div class="profile-update">Password:<br>
        <input name="input-password" type="password" placeholder="Password"><br>
      </div>
      <div class="profile-update">First Name:<br>
        <input name="input-firstname" type="text" placeholder="<?php echo $first_name ?>" 
          value="<?php echo (isset($user_json->firstname) ? $user_json->firstname : "" )?>"><br>
      </div>
      <div class="profile-update">Last Name:<br>
        <input name="input-lastname" type="text" placeholder="<?php echo $last_name ?>"
          value="<?php echo (isset($user_json->lastname) ? $user_json->lastname : "" )?>"><br>
      </div>
      <div class="profile-update">Date of Birth:<br>
        <input type="date" name="input-bday" 
          value="<?php echo (isset($user_json->dob) ? $user_json->dob : "" )?>"><br>
      </div>
      <div class="profile-update">Phone:<br>
        <input type="text" name="input-phone"
          value="<?php echo (isset($user_json->phone) ? $user_json->phone[0] : "" )?>"><br>
        </div>
      <div class="profile-update">Address:<br>
        <input type="text" name="input-address"
          value="<?php echo (isset($user_json->address) ? $user_json->address[0] : "" )?>"><br>
      </div>
      <div class="profile-update">Zipcode:<br>
        <input type="text" name="input-zipcode"
          value="<?php echo (isset($user_json->zipcode) ? $user_json->zipcode[0] : "" )?>"><br>
      </div>

      <div class="profile-update"><input class="profile-submit" type="submit" id="btn-login" name="login" class="btn-login" value="Save"></div>
    </form>
	</div>
  </body>


</html>
