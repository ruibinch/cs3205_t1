<?php

  session_start();

  $_SESSION["user_type"] = "therapist";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Patient List</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h2>Patient List</h2>

      <p>Search by name: <input type="text" id="searchbox" name="searchbox" style="width:30%"/></p>
      <table class="tb-border" width="80%">
        <tr>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Date of Birth</th>
          <th>Primary Contact Number</th>
          <th>Actions</th>
        </tr>
        <tr>
          <td>Mario</td>
          <td>Tan</td>
          <td>17/9/1992</td>
          <td>92382932</td>
          <td><a href="managep.php">View Details</a></td>
        </tr>
        <tr>
          <td>Ming Guan</td>
          <td>Chua</td>
          <td>7/7/1987</td>
          <td>82371391</td>
          <td><a href="managep.php">View Details</a></td>
        </tr>
        <tr>
          <td>Spencer</td>
          <td>Tay</td>
          <td>1/2/1993</td>
          <td>91295799</td>
          <td><a href="managep.php">View Details</a></td>
        </tr>
      </table>
    </div>

  </body>
</html>