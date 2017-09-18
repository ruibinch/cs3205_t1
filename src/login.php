<?php

  $loginSystem = "Healthcare System";
  if (isset($_GET["to"])) {
 	  if ($_GET["to"] == "console") {
  	  $loginSystem = "Management Console";
  	}
  }

?>

<html>
<meta charset="utf-8">

  <head>
    <title><?php echo $loginSystem ?></title>
    <link href="css/main.css" rel="stylesheet">
  </head>

  <body>

    <h1>Login to <?php echo $loginSystem ?></h1>
    <div class="container">
      <form class="form-signin" name="form" method="post">
        <input id="input-username" name="input-username" type="text" class="form-control" placeholder="Username" autofocus>
        <input id="input-password" name="input-password" type="password" class="form-control" placeholder="Password">

        <button id="btn-login" name="login" class="btn-login">Login</button>
      </form>

      <?php if ($loginSystem == "Healthcare System") { ?>
        <form class="form-signin-testing" name="form-testing" method="post" action="main.php">
          <p><b>For testing</b></p>
          <button name="user_type" value="patient" id="btn-login-patient" class="btn-login btn-login-patient">Login as Patient</button>
          <button name="user_type" value="therapist" id="btn-login-therapist" class="btn-login btn-login-therapist">Login as Therapist</button>
        </form>
      <?php } ?>
    </div>

  </body>
</html>
