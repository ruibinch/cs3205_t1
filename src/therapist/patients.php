<?php

  session_start();

  $_SESSION["user_type"] = "therapist";
  $patients_list = array("Katy Perry", "Nicki Minaj", "Ryan Reynolds", "Chris Hemsworth");
  $num_patients = count($patients_list);

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
      <h1>Patients you are assigned to (<?php echo $num_patients ?>):</h1>
      <hr style="margin-top:-15px">
      <p>Search by name: <input type="text" id="searchbox" name="searchbox" style="width:30%"/></p>
      <table class="main-table">
        <tr>
          <th class = "first-col">S/N</th>
          <th>Name</th>
          <th>Contact Info</th>
          <th>D.O.B</th>
        </tr>
        <?php for ($i = 0; $i < $num_patients; $i++) { ?>
          <tr>
            <td class="first-col"><?php echo ($i + 1) . "." ?></td>
            <td valign="bottom">
              <form method="post" action="managep.php">
                <input name="patient_search" value="<?php echo $patients_list[$i] ?>" type="hidden">
                <button class="list-button"><?php echo $patients_list[$i] ?></button>
              </form>
            </td>
            <td></td>
            <td></td>
          </tr>
        <?php } ?>
      </table>

    </div>

  </body>
</html>
