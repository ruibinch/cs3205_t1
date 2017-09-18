<?php

  session_start();

  $_SESSION["user_type"] = "therapist";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Manage Documents</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h2>Manage Documents</h2>

      <table class="tb-border">
        <tr>
          <th>No.</th>
          <th>Date</th>
          <th>Title</th>
          <th>Associated Patient</th>
          <th>Owner</th>
          <th>Actions</th>
        </tr>
        <tr>
          <td>1</td>
          <td>13/09/2017</td>
          <td>Report on lung condition</td>
          <td>Jenny Teo</td>
          <td>Self</td>
          <td><a href="">View</a> <a href="">Share</a></td>
        </tr>
        <tr>
          <td>2</td>
          <td>05/09/2017</td>
          <td>Bad eating habits</td>
          <td>Miranda Lee</td>
          <td>Brad Tan</td>
          <td><a href="">View</a></td>
        </tr>
        <tr>
          <td>3</td>
          <td>02/09/2017</td>
          <td>Patient's high cholesterol levels</td>
          <td>Lee Seow Jay</td>
          <td>Self</td>
          <td><a href="">View</a> <a href="">Share</a></td>
        </tr>
      </table>
    </div>

  </body>
</html>