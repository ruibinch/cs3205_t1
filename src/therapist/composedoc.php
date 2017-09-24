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

  <body class="newdoc">
    <?php include '../sidebar.php' ?>

    <div class="shifted">
      <h1>Compose a document</h1>
      <hr style="margin-top:-15px">

      <table class="newdoc-table">
        <tr>
          <td class="first-col">Associated Patient:</td>
          <td><input type="text"/>
        </tr>
        <tr>
          <td class="first-col">Title:</td>
          <td><input type="text"/></td>
        </tr>
        <tr>
          <td class="first-col">Notes:</td>
          <td><textarea class="newdoc-text"></textarea></td>
        </tr>
        <tr>
          <td class="first-col">Attach Records:</td>
          <td><a href="">View Records</a></td>
        </tr>
      </table>

      <button>Save</button>
      <button onclick="window.location.href='../main.php'">Cancel</button>
    </div>
  </body>
</html>
