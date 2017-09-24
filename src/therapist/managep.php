<?php
  session_start();
  if (isset($_POST["patient_search"])) {
    $patient = $_POST["patient_search"];
  }
  $_SESSION["user_type"] = "therapist";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Patient Management</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h1><?php echo $patient ?></h1>
      <hr style="margin-top:-15px">
      <table width="70%">
        <tr>
          <td>First Name:</td>
          <td>Mario</td>
        </tr>
        <tr>
          <td>Last Name:</td>
          <td>Tan</td>
        </tr>
        <tr>
          <td>Date of Birth:</td>
          <td>17/9/1992</td>
        </tr>
        <tr>
          <td>Primary Contact Number:</td>
          <td>92382932</td>
        </tr>
        <tr>
          <td>Secondary Contact Numbers:</td>
          <td>-</td>
        </tr>
        <tr>
          <td>Primary Address:</td>
          <td>Block 256 Sembawang North Ave 1 #03-36</td>
        </tr>
        <tr>
          <td>Secondary Addresses:</td>
          <td>-</td>
        </tr>
        <tr>
          <td>Zip code:</td>
          <td>393256</td>
        </tr>
      </table>

      <h3>Patient Records</h3>
      <p><i>(Link to viewdoc.php)</i></p>
    </div>

  </body>
</html>
