<?php

  session_start();

  $_SESSION["user_type"] = "therapist";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Compose Document</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h2>Compose Document</h2>

      <table width="80%">
        <tr>
          <td>Associated Patient:</td>
          <td><input type="text"/>
        </tr>
        <tr>
          <td>Notes:</td>
          <td><textarea></textarea></td>
        </tr>
        <tr>
          <td>Attach Records:</td>
          <td><a href="">View Records</a></td>
        </tr>
      </table>

      <button style="margin-top:50px">Save</button>
      <button onclick="window.location.href='../main.php'">Cancel</button>
    </div>
  </body>
</html>