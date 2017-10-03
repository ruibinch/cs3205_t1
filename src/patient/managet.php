<?php
  session_start();
  if (isset($_POST["therapist_search"])) {
    $therapist = $_POST["therapist_search"];
  }
  $_SESSION["user_type"] = "patient";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Therapist Management</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h1><?php echo $therapist ?></h1>
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

    </div>

  </body>
</html>
