<?php

  session_start();

  if (isset($_POST["user_type"])) {
    $user_type = $_POST["user_type"];
    $_SESSION["user_type"] = $_POST["user_type"];
  } else if (isset($_SESSION["user_type"])) {
    $user_type = $_SESSION["user_type"];
  }

?>

<html>
<meta charset="utf-8">

  <head>
    <title>Healthcare System</title>
    <link href="css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include 'sidebar.php' ?>

    <div class="shifted">
      <?php if ($user_type == "patient") { ?>
        <h1>Patient login landing page</h1>
        <p>Search list of therapists</p>
        <p>View list of assigned therapists</p>
        <p>View records</p>
        <p>View profile</p>
        <p>Logout</p>
      <?php } else if ($user_type == "therapist") { ?>
        <h1>Therapist login landing page</h1>
      <?php } ?>
    </div>

  </body>

</html>