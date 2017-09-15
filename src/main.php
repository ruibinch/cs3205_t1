<?php

  session_start();

  if (isset($_POST["userType"])) {
    $userType = $_POST["userType"];
  } else {
    $userType = $_SESSION["userType"];
  }
 
?>

<html>
<meta charset="utf-8">

  <head>
    <title>Healthcare System</title>
  </head>

  <body>

    <?php if ($userType == "patient") { ?>
      <h1>Patient login landing page</h1>
      <p>Search list of therapists</p>
      <p>View list of assigned therapists</p>
      <p>View records</p>
      <p>View profile</p>
      <p>Logout</p>
    <?php } else if ($userType == "therapist") { ?>
      <h1>Therapist login landing page</h1>
      <a href="therapist/patients.php">View patient list</a><br><br>
      <a href="therapist/composedoc.php">Compose document</a><br><br>
      <a href="therapist/managedoc.php">Manage documents</a><br><br>
      <a href="profile.php">View profile</a><br><br>
      <a href="logout.php">Logout</a>
    <?php } ?>

  </body>

</html>