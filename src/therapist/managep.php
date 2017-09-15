<?php

  session_start();

  $_SESSION["userType"] = "therapist";

?>

<html>
<meta charset="utf-8">
  <head>
    <title>Patient Management</title>
    <link href="../css/main.css" rel="stylesheet">
  </head>

  <body>
    <a href="patients.php">Back</a>
    <h2 style="margin-bottom:50px">Patient Management</h2>

    <h3>Patient Details</h3>
    <table width="50%">
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
    <p>Filter by type:
      <select>
        <option></option>
        <option>Documents</option>
        <option>Images</option>
        <option>Movies</option>
        <option>Readings</option>
        <option>Time series</option>
      </select>
    </p>
    <table class="tb-border">
      <tr>
        <th>No.</th>
        <th>Date</th>
        <th>Type</th>
        <th>Subtype</th>
        <th>Title</th>
        <th>Actions</th>
      </tr>
      <tr>
        <td>1</td>
        <td>12/08/2017</td>
        <td>Readings</td>
        <td>BP</td>
        <td>Dizzy spell</td>
        <td><a href="">View</a></td>
      </tr>
      <tr>
        <td>2</td>
        <td>12/08/2017</td>
        <td>Images</td>
        <td>MRI</td>
        <td>LS spine S4</td>
        <td><a href="">View</a></td>
      </tr>
      <tr>
        <td>3</td>
        <td>12/08/2017</td>
        <td>Time series</td>
        <td>Gait</td>
        <td>Left foot rotation</td>
        <td><a href="">View</a></td>
      </tr>
      <tr>
        <td>4</td>
        <td>12/08/2017</td>
        <td>Movies</td>
        <td>Gait</td>
        <td>Assessment</td>
        <td><a href="">View</a></td>
      </tr>
      <tr>
        <td>5</td>
        <td>12/08/2017</td>
        <td>Documents</td>
        <td>Report</td>
        <td>Report on gait assessment</td>
        <td><a href="">View</a></td>
      </tr>
    </table>
  </body>
</html>