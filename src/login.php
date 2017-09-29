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
    <title>Login</title>
    <link href="css/main.css" rel="stylesheet">
  </head>

  <body class="login">
    <h1>
      <font size="30px" face="Lucida Grande" color="white">CS3205 Team 1</font><br>
      <font size="5px" face="Lucida Grande" color="white" id="loginsystem"></font>
    </h1>
    <div class="login-container">
      <div class="login-tab">
        <button class="tablinks user-tab active" onclick="openTab(event, 'User'), toggleLoginSystem('User')">User</button>
        <button class="tablinks mgmt-tab" onclick="openTab(event, 'Management'), toggleLoginSystem('Management')">Management</button>

        <div id="User" class="login-tabcontent" style="display: block;">
          <form class="login-form" id="form-user" name="form-user" method="post" action="main.php">
            <input id="username" name="username" type="text" class="login-input" placeholder="Username" autofocus>
            <input id="password" name="password" type="password" class="login-input" placeholder="Password">
            <button name="user_type" value="patient" id="btn-login-patient" class="login-btn login-btn-patient">Login as Patient</button>
            <button name="user_type" value="therapist" id="btn-login-therapist" class="login-btn login-btn-therapist">Login as Therapist</button>
          </form>
        </div>

        <div id="Management" class="login-tabcontent">
          <form class="login-form" name="form-mgmt" method="post" action="management/process.php">
            <input id="mgmt-username" name="mgmt-username" type="text" class="login-input" placeholder="Username" autofocus>
            <input id="mgmt-password" name="mgmt-password" type="password" class="login-input" placeholder="Password">
            <button id="btn-login" name="login" class="login-btn login-btn-mgmt">Login</button>
          </form>
		  <?php
			if (isset($_GET['err']) && $_GET['err'] === "1") {
				echo '<br/><br/><h3 style="color: red;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;Invalid username or password.</h3>' . "\n";
			}
		  ?>		  
        </div>
      </div>
    </div>

    <script>

    window.onload = function() {
      document.getElementById("loginsystem").innerHTML = "<?php echo $loginSystem ?>";
	  <?php
		//test code to default to management tab if it is selected. feel free to remove if desired.
		//small bug: dk how to change to active class for management..... javascript rusty liao. zz
		//it is broken on firefox too... 
		if ($loginSystem === "Management Console") {
			echo 'openTab(event, \'Management\');' . "\n";
			echo 'toggleLoginSystem(\'Management\');' . "\n";
		}
	  ?>
    }

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

    function toggleLoginSystem(tabName) {
      if (tabName == "User") {
        document.getElementById("loginsystem").innerHTML = "Healthcare System";
        openTab(event, 'User');
      } else if (tabName == "Management") {
        document.getElementById("loginsystem").innerHTML = "Management Console";
        openTab(event, 'Management');
      }
    }
    </script>

  </body>
</html>
