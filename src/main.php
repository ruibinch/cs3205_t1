<?php

  $user = $_POST["user"];

?>

<html>
<meta charset="utf-8">

  <head>
    <title>Healthcare System</title>
  </head>

  <body>

    <?php if ($user == "patient") { ?>
      <h1>Patient login landing page</h1>
      <p>Search list of therapists</p>
      <p>View list of assigned therapists</p>
      <p>View records</p>
      <p>View profile</p>
      <p>Logout</p>
    <?php } else if ($user == "therapist") { ?>
      <h1>Therapist login landing page</h1>
      <a href="patients.php">View list of assigned patients</a><br><br>
      <a href="composedoc.php">Compose document</a><br><br>
      <a href="managedoc.php">View documents</a><br><br>
      <a href="profile.php">View profile</a><br><br>
      <a href="logout.php">Logout</a>
    <?php } ?>

  </body>

</html>