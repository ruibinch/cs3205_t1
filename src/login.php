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

  <body class="login">
    <!-- <h1 style="text-align:center">Login to <?php echo $loginSystem ?></h1> -->
    <h1>
      <font size="30px" face="Lucida Grande" color="white">CS3205 Team 1</font><br>
      <font size="5px" face="Lucida Grande" color="white"><?php echo strtoupper($loginSystem) ?></font>
    </h1>
    <div class="login-container">
      <div class="login-tab">
        <button class="tablinks user-tab active" onclick="openTab(event, 'User')">User</button>
        <button class="tablinks mgmt-tab" onclick="openTab(event, 'Management')">Management</button>

        <div id="User" class="login-tabcontent" style="display: block;">
          <form class="login-form" id="form-user" name="form-user" method="post" action="main.php">
            <input id="username" name="username" type="text" class="login-input" placeholder="Username" autofocus>
            <input id="password" name="password" type="password" class="login-input" placeholder="Password">
            <button name="user_type" value="patient" id="btn-login-patient" class="login-btn login-btn-patient">Login as Patient</button>
            <button name="user_type" value="therapist" id="btn-login-therapist" class="login-btn login-btn-therapist">Login as Therapist</button>
          </form>
        </div>

        <div id="Management" class="login-tabcontent">
          <form class="login-form" name="form-mgmt" method="post">
            <input id="mgmt-username" name="mgmt-username" type="text" class="login-input" placeholder="Username" autofocus>
            <input id="mgmt-password" name="mgmt-password" type="password" class="login-input" placeholder="Password">
            <button id="btn-login" name="login" class="login-btn login-btn-mgmt">Login</button>
          </form>
        </div>
      </div>
    </div>

    <script>
    function openTab(evt, tabName) {
        var i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("login-tabcontent");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "none";
        }
        tablinks = document.getElementsByClassName("tablinks");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].className = tablinks[i].className.replace(" active", "");
        }
        document.getElementById(tabName).style.display = "block";
        evt.currentTarget.className += " active";
    }
    </script>

  </body>
</html>